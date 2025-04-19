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
    // app/Http/Controllers/PostsController.php

    public function index(Request $request)
    {
        // server‑side paginator (we’ll no longer use these links)
        $postsPaged = Post::orderBy('created_at', 'desc')
            ->paginate(10);

        // full collection for client‑side use
        $postsAll = Post::select('id', 'title', 'slug', 'type', 'status', 'featured_image', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $types = ['post' => 'Post', 'page' => 'Page', 'custom' => 'Custom'];
        $statuses = ['published' => 'Published', 'draft' => 'Draft'];

        return view('posts.index', compact('postsAll', 'types', 'statuses'));
    }

    public function create()
    {
        $templates = getThemeTemplates(); // ✅ Load from theme.json
        return view('posts.create', compact('templates'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:posts,slug',
            'type' => 'required|string',
            'status' => 'required|string',
            'content' => 'nullable|string',
            'template' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }

        $post = Post::create($validated);

        // Save metadata
        if ($request->meta) {
            foreach ($request->meta as $key => $value) {
                PostMeta::create([
                    'post_id' => $post->id,
                    'meta_key' => $key,
                    'meta_value' => $value
                ]);
            }
        }

        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }

    public function edit(Post $post)
    {
        $templates = getThemeTemplates(); // ✅ Load from theme.json
        return view('posts.edit', compact('post', 'templates'));
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
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image && Storage::disk('public')->exists($post->featured_image)) {
                Storage::disk('public')->delete($post->featured_image);
            }

            $validated['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }

        $post->update($validated);

        // Update metadata
        if ($request->meta) {
            foreach ($request->meta as $key => $value) {
                $post->meta()->updateOrCreate(['meta_key' => $key], [
                    'meta_value' => $value
                ]);
            }
        }

        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        if ($post->featured_image && Storage::disk('public')->exists($post->featured_image)) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }
    /**
     * Display a single “page” by slug.
     */
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

        // if a custom template exists for this page…
        if ($page->template && view()->exists($templateView)) {
            return view($templateView, compact('page', 'site', 'themeSettings', 'pageOutput'));
        }

        // otherwise fall back to the default page.blade.php
        return view($defaultView, compact('page', 'site', 'themeSettings', 'pageOutput'));
    }

    /**
     * Convert the stored JSON in the `block` field to HTML.
     */
    private function buildPageOutput(Post $page)
    {
        // use the `block` column instead of `content`
        $json = $page->block;

        // ensure we always pass a valid JSON array string
        if (!is_string($json) || trim($json) === '') {
            $json = '[]';
        }

        return ElementFactory::json2html($json);
    }

}