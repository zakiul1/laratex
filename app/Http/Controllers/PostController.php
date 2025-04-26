<?php

namespace App\Http\Controllers;

use Aponahmed\HtmlBuilder\ElementFactory;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\TermTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        return view('posts.index', compact('postsAll', 'types', 'statuses'));
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:posts,slug',
            'type' => 'required|string',
            'status' => 'required|string',
            'content' => 'nullable|string',
            'template' => 'nullable|string',
            'featured_images' => 'nullable|array',
            'featured_images.*' => 'integer|exists:media,id',
            'meta' => 'nullable|array',
            'meta.*' => 'nullable|string',
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:term_taxonomies,id',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();

        // Create the Post
        $post = Post::create($validated);

        // Save metas
        if (!empty($validated['meta'])) {
            foreach ($validated['meta'] as $key => $value) {
                PostMeta::create([
                    'post_id' => $post->id,
                    'meta_key' => $key,
                    'meta_value' => $value,
                ]);
            }
        }

        // SEO
        $post->seoMeta()->updateOrCreate([], ['meta' => $validated['seo']]);

        // Categories
        $post->categories()->sync($validated['categories'] ?? []);

        // Featured images (stored as JSON array on `featured_images` column)
        $post->featured_images = $validated['featured_images'] ?? [];
        $post->save();

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
        $selected = $post->categories->pluck('id')->toArray();

        return view('posts.edit', compact('post', 'templates', 'allCategories', 'selected'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:posts,slug,' . $post->id,
            'type' => 'required|string',
            'status' => 'required|string',
            'content' => 'nullable|string',
            'template' => 'nullable|string',
            'featured_images' => 'nullable|array',
            'featured_images.*' => 'integer|exists:media,id',
            'meta' => 'nullable|array',
            'meta.*' => 'nullable|string',
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:term_taxonomies,id',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);

        // Update basic fields
        $post->update($validated);

        // Update metas
        if (!empty($validated['meta'])) {
            foreach ($validated['meta'] as $key => $value) {
                $post->meta()->updateOrCreate(
                    ['meta_key' => $key],
                    ['meta_value' => $value]
                );
            }
        }

        // SEO
        $post->seoMeta()->updateOrCreate([], ['meta' => $validated['seo']]);

        // Categories
        $post->categories()->sync($validated['categories'] ?? []);

        // Featured images
        $post->featured_images = $validated['featured_images'] ?? [];
        $post->save();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

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
}