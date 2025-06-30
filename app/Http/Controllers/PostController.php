<?php

namespace App\Http\Controllers;

use Aponahmed\HtmlBuilder\ElementFactory;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\SiteSetting;
use App\Models\Term;
use App\Models\TermTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        // Base query with eager‐loading
        $query = Post::with('taxonomies.term');

        // Optional category filter
        if ($cid = $request->get('filter_category')) {
            $query->whereHas('taxonomies', fn($q) => $q->where('term_taxonomy_id', $cid));
        }

        // 1) All posts
        $postsAll = (clone $query)
            ->orderBy('created_at', 'desc')
            ->get();

        // 2) Paginated posts
        $postsPaged = (clone $query)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Category dropdown data
        $allCategories = TermTaxonomy::select('term_taxonomies.*')
            ->join('terms', 'term_taxonomies.term_id', '=', 'terms.id')
            ->where('taxonomy', 'category')
            ->with('term')
            ->orderBy('terms.name')
            ->get();

        $types = ['post' => 'Post', 'page' => 'Page', 'custom' => 'Custom'];
        $statuses = ['published' => 'Published', 'draft' => 'Draft'];

        return view('posts.index', compact(
            'postsAll',
            'postsPaged',
            'allCategories',
            'types',
            'statuses'
        ));
    }

    public function create()
    {
        $templates = getThemeTemplates();
        $post = new Post(['type' => 'post']);
        $allCategories = TermTaxonomy::with('term')->where('taxonomy', 'category')->get();
        $selected = [];

        // Ensure the view has an empty $seo array on create
        $seo = [];

        return view('posts.create', compact(
            'templates',
            'post',
            'allCategories',
            'selected',
            'seo'
        ));
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

        // 2) Validate
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', Rule::unique('posts', 'slug')],
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

        // attach author
        $validated['author_id'] = auth()->id();

        // featured images JSON
        $validated['featured_images'] = array_values(
            array_unique($validated['featured_media_ids'] ?? [], SORT_NUMERIC)
        );

        // 3) Create post
        $post = Post::create($validated);

        // 4) Custom metas (non-SEO)
        if (!empty($validated['meta'])) {
            foreach ($validated['meta'] as $m) {
                PostMeta::create([
                    'post_id' => $post->id,
                    'meta_key' => $m['key'],
                    'meta_value' => $m['value'],
                ]);
            }
        }

        // 5) Save the SEO payload as a single PostMeta row with meta_key = "seo"
        $post->seoMeta()->updateOrCreate(
            ['meta_key' => 'seo'],
            ['meta_value' => json_encode($validated['seo'] ?? [])]
        );

        // 6) Categories (with default fallback)
        $categoryIds = $validated['categories'] ?? [];
        if (empty($categoryIds)) {
            $term = Term::firstOrCreate(
                ['slug' => 'uncategorized'],
                ['name' => 'Uncategorized']
            );
            $tax = TermTaxonomy::firstOrCreate(
                ['taxonomy' => 'category', 'term_id' => $term->id],
                ['parent' => 0, 'status' => 1, 'description' => null, 'count' => 0]
            );
            $categoryIds = [$tax->term_taxonomy_id];
        }
        $post->syncCategories($categoryIds);

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function edit(Post $post)
    {
        $templates = getThemeTemplates();
        $allCategories = TermTaxonomy::with('term')->where('taxonomy', 'category')->get();
        $selected = $post->categories->pluck('term_taxonomy_id')->toArray();

        // Custom metas (non-SEO, non-featured_image)
        $customMeta = $post->meta()
            ->whereNotIn('meta_key', ['seo', 'featured_image'])
            ->get()
            ->map(fn($m) => ['key' => $m->meta_key, 'value' => $m->meta_value])
            ->toArray();

        // Normalize SEO into a PHP array and assign to $seo
        $rawSeo = optional($post->seoMeta)->meta_value;
        $seo = is_string($rawSeo)
            ? json_decode($rawSeo, true)
            : (is_array($rawSeo) ? $rawSeo : []);

        // Initial featured for Alpine
        $initialFeatured = collect(
            old('featured_media_ids', $post->featured_images ?? [])
        )
            ->map(fn($id) => ($m = Media::find($id)) ? ['id' => $m->id, 'url' => $m->getUrl('thumbnail')] : null)
            ->filter()
            ->values()
            ->toArray();

        return view('posts.edit', compact(
            'post',
            'templates',
            'allCategories',
            'selected',
            'customMeta',
            'seo',
            'initialFeatured'
        ));
    }

    public function update(Request $request, Post $post)
    {
        // Slug dedupe
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

        // Validate
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

        // Featured images array
        $validated['featured_images'] = array_values(
            array_unique($validated['featured_media_ids'] ?? [], SORT_NUMERIC)
        );

        // Update the “posts” table
        $post->update($validated);

        // Delete old custom metas, then re‐create them
        $post->meta()
            ->whereNotIn('meta_key', ['seo', 'featured_image'])
            ->delete();

        foreach ($validated['meta'] ?? [] as $m) {
            $post->meta()->updateOrCreate(
                ['meta_key' => $m['key']],
                ['meta_value' => $m['value']]
            );
        }

        // Update or create the single “seo” PostMeta row
        $post->seoMeta()->updateOrCreate(
            ['meta_key' => 'seo'],
            ['meta_value' => json_encode($validated['seo'] ?? [])]
        );

        // Categories fallback
        $categoryIds = $validated['categories'] ?? [];
        if (empty($categoryIds)) {
            $term = Term::firstOrCreate(
                ['slug' => 'uncategorized'],
                ['name' => 'Uncategorized']
            );
            $tax = TermTaxonomy::firstOrCreate(
                ['taxonomy' => 'category', 'term_id' => $term->id],
                ['parent' => 0, 'status' => 1, 'description' => null, 'count' => 0]
            );
            $categoryIds = [$tax->term_taxonomy_id];
        }
        $post->syncCategories($categoryIds);

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Request $request, Post $post)
    {
        $post->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Post deleted'], 200);
        }

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    private function buildPageOutput(Post $page)
    {
        return ElementFactory::json2html($page->block ?: '[]');
    }

    public function ajaxCategoryStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent' => ['nullable', 'integer', Rule::exists('term_taxonomies', 'term_taxonomy_id')],
        ]);

        $slug = Str::slug($data['name']);
        if (Term::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Category already exists'], 409);
        }

        $term = Term::create(['name' => $data['name'], 'slug' => $slug]);
        $tt = TermTaxonomy::create([
            'term_id' => $term->id,
            'taxonomy' => 'category',
            'parent' => $data['parent'] ?? 0,
            'status' => 1,
            'description' => null,
            'count' => 0,
        ]);

        return response()->json([
            'id' => $tt->term_taxonomy_id,
            'name' => $term->name,
            'parent' => $tt->parent,
        ]);
    }




    public function show(string $slug)
    {
        // 1) Load the post by slug, eager‐load its taxonomies + term
        $post = Post::with(['taxonomies.term'])
            ->where('slug', $slug)
            ->firstOrFail();

        // 2) Convert its JSON‐stored content into HTML
        $pageOutput = ElementFactory::json2html($post->content ?: '[]');

        // 3) Grab the first category taxonomy for the “category” variable
        $categoryTaxonomy = $post->taxonomies->first();

        // 4) (Optional) If you want a “Featured Posts” block like your products:
        $featuredCategory = TermTaxonomy::with(['term', 'posts']) // make sure TermTaxonomy::posts() exists
            ->where('taxonomy', 'post')
            ->whereHas('term', fn($q) => $q->where('name', 'Featured Posts'))
            ->first();
        $featuredPosts = $featuredCategory ? $featuredCategory->posts : collect();

        // 5) Figure out which theme to use
        if (Schema::hasTable('site_settings')) {
            $settings = SiteSetting::firstOrCreate([]);
            $theme = $settings->active_theme ?: 'classic';
        } else {
            $theme = env('ACTIVE_THEME', 'classic');
        }

        // 6) Render the theme‐specific blade
        $view = "themes.{$theme}.templates.post";
        abort_unless(view()->exists($view), 404, "Template not found: {$view}");

        return view($view, [
            'post' => $post,
            'category' => $categoryTaxonomy,
            'featuredCategory' => $featuredCategory,
            'featuredPosts' => $featuredPosts,
            'pageOutput' => $pageOutput,
        ]);
    }
}