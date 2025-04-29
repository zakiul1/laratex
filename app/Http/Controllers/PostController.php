<?php

namespace App\Http\Controllers;

use Aponahmed\HtmlBuilder\ElementFactory;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\Term;
use App\Models\TermTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $postsPaged = Post::orderBy('created_at', 'desc')
            ->paginate(10);

        $postsAll = Post::select('id', 'title', 'slug', 'type', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $types = ['post' => 'Post', 'page' => 'Page', 'custom' => 'Custom'];
        $statuses = ['published' => 'Published', 'draft' => 'Draft'];

        return view('posts.index', compact('postsAll', 'types', 'statuses', 'postsPaged'));
    }

    public function create()
    {
        $templates = getThemeTemplates();
        $post = new Post(['type' => 'post']);
        $allCategories = TermTaxonomy::with('term')
            ->where('taxonomy', 'category')
            ->get();
        $selected = [];

        return view('posts.create', compact('templates', 'post', 'allCategories', 'selected'));
    }

    public function store(Request $request)
    {
        // 1) Build & dedupe the slug BEFORE validation:
        $input = $request->all();
        $input['slug'] = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('title'));

        $base = $input['slug'];
        $i = 1;
        while (Post::where('slug', $input['slug'])->exists()) {
            $input['slug'] = "{$base}-" . $i++;
        }

        // Merge back into the request so the validator sees the final slug
        $request->merge($input);

        // 2) Now validate, including a UNIQUE rule on the real slug
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', Rule::unique('posts', 'slug')],
            'type' => 'required|string',
            'status' => 'required|string',
            'content' => 'nullable|string',
            'template' => 'nullable|string',
            'featured_images' => 'nullable|array',
            'featured_images.*' => 'integer|exists:media,id',
            'meta' => 'nullable|array',
            'meta.*.key' => 'required|string|min:1',
            'meta.*.value' => 'nullable|string',
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:term_taxonomies,term_taxonomy_id',
        ]);

        $validated['author_id'] = auth()->id();

        // Ensure unique featured_images
        if (!empty($validated['featured_images'])) {
            $validated['featured_images'] = array_values(
                array_unique($validated['featured_images'], SORT_NUMERIC)
            );
        }

        // 3) Create the post
        $post = Post::create($validated);

        // 4) Save other related data
        if (!empty($validated['meta'])) {
            foreach ($validated['meta'] as $meta) {
                PostMeta::create([
                    'post_id' => $post->id,
                    'meta_key' => $meta['key'],
                    'meta_value' => $meta['value'],
                ]);
            }
        }

        // SEO
        $post->seoMeta()->updateOrCreate(
            ['meta_key' => 'seo'],
            ['meta_value' => json_encode($validated['seo'])]
        );

        // Categories & images
        $post->syncCategories($validated['categories'] ?? []);
        $post->syncFeaturedImages($validated['featured_images'] ?? []);

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function edit(Post $post)
    {
        $templates = getThemeTemplates();

        $allCategories = TermTaxonomy::with('term')
            ->where('taxonomy', 'category')
            ->get();

        $selected = $post->categories
            ->pluck('term_taxonomy_id')
            ->toArray();

        // pull ALL metas except seo/featured_image
        $customMeta = $post->meta()
            ->whereNotIn('meta_key', ['seo', 'featured_image'])
            ->get()
            ->map(fn($m) => [
                'key' => $m->meta_key,
                'value' => $m->meta_value,
            ])
            ->toArray();  // convert to plain array for your Blade/Alpine

        // ── Normalize SEO meta_value ───────────────────────────────────
        $rawSeo = optional($post->seoMeta)->meta_value;
        if (is_string($rawSeo)) {
            // it was stored as JSON string
            $seoMeta = json_decode($rawSeo, true) ?: [];
        } elseif (is_array($rawSeo)) {
            // already cast to array by model
            $seoMeta = $rawSeo;
        } else {
            $seoMeta = [];
        }

        // get the featured-image IDs array
        $featuredImages = $post->getFeaturedImageIdsAttribute();

        return view('posts.edit', compact(
            'post',
            'templates',
            'allCategories',
            'selected',
            'customMeta',
            'seoMeta',
            'featuredImages'
        ));
    }



    public function update(Request $request, Post $post)
    {
        // 1) Build & dedupe slug BEFORE validation, ignoring current post
        $input = $request->all();
        $input['slug'] = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('title'));

        $base = $input['slug'];
        $i = 1;
        while (
            Post::where('slug', $input['slug'])
                ->where('id', '!=', $post->id)
                ->exists()
        ) {
            $input['slug'] = "{$base}-" . $i++;
        }

        $request->merge($input);

        // 2) Validate, ignoring this post for unique check
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                Rule::unique('posts', 'slug')->ignore($post->id),
            ],
            'type' => 'required|string',
            'status' => 'required|string',
            'content' => 'nullable|string',
            'template' => 'nullable|string',
            'featured_images' => 'nullable|array',
            'featured_images.*' => 'integer|exists:media,id',
            'meta' => 'nullable|array',
            'meta.*.key' => 'required|string|min:1',
            'meta.*.value' => 'nullable|string',
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:term_taxonomies,term_taxonomy_id',
        ]);

        // Unique featured_images
        if (!empty($validated['featured_images'])) {
            $validated['featured_images'] = array_values(
                array_unique($validated['featured_images'], SORT_NUMERIC)
            );
        }

        // 3) Update
        $post->update($validated);

        // 4) Sync metas (except seo), SEO, categories, images
        if (!empty($validated['meta'])) {
            $post->meta()
                ->whereNotIn('meta_key', ['seo', 'featured_image'])
                ->delete();

            foreach ($validated['meta'] as $meta) {
                $post->meta()->updateOrCreate(
                    ['meta_key' => $meta['key']],
                    ['meta_value' => $meta['value']]
                );
            }
        }

        $post->seoMeta()->updateOrCreate(
            ['meta_key' => 'seo'],
            ['meta_value' => json_encode($validated['seo'])]
        );

        $post->syncCategories($validated['categories'] ?? []);
        $post->syncFeaturedImages($validated['featured_images'] ?? []);

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * AJAX: create a new product category
     */

    public function destroy(Post $post)
    {
        $post->delete();

        if (request()->wantsJson()) {
            // 204 No Content is ideal for a successful delete with no response body
            return response()->json(null, 204);
        }

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }


    public function show($slug)
    {
        $page = Post::where('slug', $slug)
            ->where('type', 'post')
            ->where('status', 'Published')
            ->firstOrFail();

        $theme = getActiveTheme();
        $templateView = "themes.{$theme}.templates.{$page->template}";
        $defaultView = "themes.{$theme}.page";
        $pageOutput = $this->buildPageOutput($page);
        $site = site_settings();
        $themeSettings = theme_settings();

        if ($page->template && view()->exists($templateView)) {
            return view($templateView, compact('page', 'site', 'themeSettings', 'pageOutput'));
        }

        return view($defaultView, compact('page', 'site', 'themeSettings', 'pageOutput'));
    }

    private function buildPageOutput(Post $page)
    {
        $json = $page->block ?: '[]';
        return ElementFactory::json2html($json);
    }



    /**
     * AJAX: Create a new post‐category on the fly (with optional parent).
     */
    public function ajaxCategoryStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent' => ['nullable', 'integer', Rule::exists('term_taxonomies', 'term_taxonomy_id')],
            'status' => ['nullable', 'integer'], // if you want to set status, or omit
        ]);

        // slug‐dedupe
        $slug = Str::slug($data['name']);
        if (Term::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Category already exists'], 409);
        }

        // 1) create term
        $term = Term::create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        // 2) create taxonomy row
        $tax = TermTaxonomy::create([
            'term_id' => $term->id,
            'taxonomy' => 'category',                 // since these are post categories
            'parent' => $data['parent'] ?? 0,
            'status' => $data['status'] ?? 1,       // if you track status
        ]);

        // return the new item
        return response()->json([
            'id' => $tax->term_taxonomy_id,
            'name' => $term->name,
            'parent' => $tax->parent,
        ]);
    }

}