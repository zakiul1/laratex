<?php

namespace App\Http\Controllers;

use Aponahmed\HtmlBuilder\ElementFactory;
use App\Models\Media;
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
        $postsPaged = Post::orderBy('created_at', 'desc')->paginate(10);
        $postsAll = Post::select('id', 'title', 'slug', 'type', 'status', 'created_at')->orderBy('created_at', 'desc')->get();
        $types = ['post' => 'Post', 'page' => 'Page', 'custom' => 'Custom'];
        $statuses = ['published' => 'Published', 'draft' => 'Draft'];
        return view('posts.index', compact('postsAll', 'types', 'statuses', 'postsPaged'));
    }

    public function create()
    {
        $templates = getThemeTemplates();
        $post = new Post(['type' => 'post']);
        $allCategories = TermTaxonomy::with('term')->where('taxonomy', 'category')->get();
        $selected = [];
        return view('posts.create', compact('templates', 'post', 'allCategories', 'selected'));
    }

    public function store(Request $request)
    {
        // 1) Build & dedupe slug
        $input = $request->all();

        $input['slug'] = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('title'));
        $base = $input['slug'];
        $i = 1;
        while (Post::where('slug', $input['slug'])->exists()) {
            $input['slug'] = "{$base}-" . $i++;
        }
        $request->merge($input);

        // 2) Validate, including featured_media_ids
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', Rule::unique('posts', 'slug')],
            'type' => 'required|string',
            'status' => 'required|string',
            'content' => 'nullable|string',
            'template' => 'nullable|string',

            // use featured_media_ids to match product controller
            'featured_media_ids' => 'nullable|array',
            'featured_media_ids.*' => 'integer|exists:media,id',

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

        // attach author
        $validated['author_id'] = auth()->id();

        // ensure unique IDs
        $featured = array_values(array_unique($validated['featured_media_ids'] ?? [], SORT_NUMERIC));
        $validated['featured_images'] = $featured;

        // 3) Create post
        $post = Post::create($validated);

        // 4) metas
        if (!empty($validated['meta'])) {
            foreach ($validated['meta'] as $m) {
                PostMeta::create(['post_id' => $post->id, 'meta_key' => $m['key'], 'meta_value' => $m['value']]);
            }
        }
        $post->seoMeta()->updateOrCreate(['meta_key' => 'seo'], ['meta_value' => json_encode($validated['seo'] ?? [])]);

        // 5) categories
        $post->syncCategories($validated['categories'] ?? []);

        // 6) featured images JSON stored in model via cast, no extra sync needed

        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }

    public function edit(Post $post)
    {
        $templates = getThemeTemplates();
        $allCategories = TermTaxonomy::with('term')->where('taxonomy', 'category')->get();
        $selected = $post->categories->pluck('term_taxonomy_id')->toArray();

        // custom metas
        $customMeta = $post->meta()->whereNotIn('meta_key', ['seo', 'featured_image'])
            ->get()->map(fn($m) => ['key' => $m->meta_key, 'value' => $m->meta_value])->toArray();

        // normalize SEO
        $rawSeo = optional($post->seoMeta)->meta_value;
        $seoMeta = is_string($rawSeo) ? json_decode($rawSeo, true) : (is_array($rawSeo) ? $rawSeo : []);

        // initial featured array for Alpine
        $initialFeatured = collect(old('featured_media_ids', $post->featured_images ?? []))
            ->map(fn($id) => ($m = Media::find($id)) ? ['id' => $m->id, 'url' => $m->getUrl('thumbnail')] : null)
            ->filter()->values()->toArray();
        // dd($initialFeatured);

        return view('posts.edit', compact(
            'post',
            'templates',
            'allCategories',
            'selected',
            'customMeta',
            'seoMeta',
            'initialFeatured'
        ));
    }

    public function update(Request $request, Post $post)
    {
        // similar slug dedupe
        $input = $request->all();
        $input['slug'] = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('title'));
        $base = $input['slug'];
        $i = 1;
        while (Post::where('slug', $input['slug'])->where('id', '!=', $post->id)->exists()) {
            $input['slug'] = "{$base}-" . $i++;
        }
        $request->merge($input);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', Rule::unique('posts', 'slug')->ignore($post->id)],
            'type' => 'required|string',
            'status' => 'required|string',
            'content' => 'nullable|string',
            'template' => 'nullable|string',
            'featured_media_ids' => 'nullable|array',
            'featured_media_ids.*' => 'integer|exists:media,id',
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

        // unique featured IDs
        $featured = array_values(array_unique($validated['featured_media_ids'] ?? [], SORT_NUMERIC));
        $validated['featured_images'] = $featured;

        $post->update($validated);

        // metas
        $post->meta()->whereNotIn('meta_key', ['seo', 'featured_image'])->delete();
        foreach ($validated['meta'] ?? [] as $m) {
            $post->meta()->updateOrCreate(['meta_key' => $m['key']], ['meta_value' => $m['value']]);
        }
        $post->seoMeta()->updateOrCreate(['meta_key' => 'seo'], ['meta_value' => json_encode($validated['seo'] ?? [])]);

        // categories
        $post->syncCategories($validated['categories'] ?? []);

        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }

    private function buildPageOutput(Post $page)
    {
        return ElementFactory::json2html($page->block ?: '[]');
    }

    public function ajaxCategoryStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent' => ['nullable', 'integer', Rule::exists('term_taxonomies', 'term_taxonomy_id')]
        ]);
        $slug = Str::slug($data['name']);
        if (Term::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Category already exists'], 409);
        }
        $term = Term::create(['name' => $data['name'], 'slug' => $slug]);
        $tt = TermTaxonomy::create(['term_id' => $term->id, 'taxonomy' => 'category', 'parent' => $data['parent'] ?? 0, 'status' => 1, 'description' => null, 'count' => 0]);
        return response()->json(['id' => $tt->term_taxonomy_id, 'name' => $term->name, 'parent' => $tt->parent]);
    }
}