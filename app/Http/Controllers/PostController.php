<?php

namespace App\Http\Controllers;

use Aponahmed\HtmlBuilder\ElementFactory;
use App\Models\Post;
use App\Models\PostMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $postsPaged = Post::orderBy('created_at', 'desc')
            ->paginate(10);

        $postsAll = Post::select('id', 'title', 'slug', 'type', 'status', 'featured_image', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $types = ['post' => 'Post', 'page' => 'Page', 'custom' => 'Custom'];
        $statuses = ['published' => 'Published', 'draft' => 'Draft'];

        return view('posts.index', compact('postsAll', 'types', 'statuses'));
    }

    public function create()
    {
        $templates = getThemeTemplates();

        // give Blade a blank model to pull old values or defaults
        $post = new Post(['type' => 'post']);

        return view('posts.create', compact('templates', 'post'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // ─── core fields ─────────────────────────────────────────────
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:posts,slug',
            'type' => 'required|string',
            'status' => 'required|string',
            'content' => 'nullable|string',
            'template' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // ─── generic PostMeta (you already use) ──────────────────────
            'meta' => 'nullable|array',
            'meta.*' => 'nullable|string',

            // ─── NEW: SEO fields ───────────────────────────────────────────
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
        ]);

        // slug fallback
        $validated['slug'] = $validated['slug']
            ?? Str::slug($validated['title']);

        $validated['author_id'] = auth()->id();

        // handle featured image
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request
                ->file('featured_image')
                ->store('posts', 'public');
        }

        // create the post record
        $post = Post::create($validated);

        // Save your existing PostMeta entries
        if (!empty($validated['meta'])) {
            foreach ($validated['meta'] as $key => $value) {
                PostMeta::create([
                    'post_id' => $post->id,
                    'meta_key' => $key,
                    'meta_value' => $value,
                ]);
            }
        }

        // ─── NEW: sync SEO JSON into seo_meta ──────────────────────────
        $post->seoMeta()->updateOrCreate(
            [],
            ['meta' => $validated['seo']]
        );

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function edit(Post $post)
    {
        $templates = getThemeTemplates();

        return view('posts.edit', compact('post', 'templates'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            // ─── core fields ─────────────────────────────────────────────
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:posts,slug,' . $post->id,
            'type' => 'required|string',
            'status' => 'required|string',
            'content' => 'nullable|string',
            'template' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // ─── generic PostMeta ────────────────────────────────────────
            'meta' => 'nullable|array',
            'meta.*' => 'nullable|string',

            // ─── NEW: SEO fields ───────────────────────────────────────────
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
        ]);

        // slug fallback
        $validated['slug'] = $validated['slug']
            ?? Str::slug($validated['title']);

        // handle featured image replacement
        if ($request->hasFile('featured_image')) {
            if (
                $post->featured_image
                && Storage::disk('public')->exists($post->featured_image)
            ) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $validated['featured_image'] = $request
                ->file('featured_image')
                ->store('posts', 'public');
        }

        // update the core Post
        $post->update($validated);

        // update generic PostMeta entries
        if (!empty($validated['meta'])) {
            foreach ($validated['meta'] as $key => $value) {
                $post->meta()
                    ->updateOrCreate(
                        ['meta_key' => $key],
                        ['meta_value' => $value]
                    );
            }
        }

        // ─── NEW: update SEO JSON blob ────────────────────────────────
        $post->seoMeta()->updateOrCreate(
            [],
            ['meta' => $validated['seo']]
        );

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        if (
            $post->featured_image
            && Storage::disk('public')->exists($post->featured_image)
        ) {
            Storage::disk('public')->delete($post->featured_image);
        }
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
            return view(
                $templateView,
                compact('page', 'site', 'themeSettings', 'pageOutput')
            );
        }

        return view(
            $defaultView,
            compact('page', 'site', 'themeSettings', 'pageOutput')
        );
    }

    private function buildPageOutput(Post $page)
    {
        $json = $page->block ?: '[]';
        return ElementFactory::json2html($json);
    }
}