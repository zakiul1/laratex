<?php

namespace App\Http\Controllers;

use Aponahmed\HtmlBuilder\ElementFactory;
use App\Models\Contact;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Show the front‐page (or redirect to a named page slug).
     */
    public function home()
    {
        // If you have a SiteSetting home‐slug, use it; otherwise just list pages
        $settings = SiteSetting::firstOrCreate([]);
        $slug = $settings->home_page_slug ?: 'home';

        // If you want to render a “page” template:
        return $this->show($slug);
    }

    public function index()
    {
        $pagesAll = Post::where('type', 'page')
            ->select('id', 'slug', 'title', 'status', 'template', 'featured_images', 'created_at')
            ->latest()
            ->get();

        $statuses = ['published' => 'Published', 'draft' => 'Draft'];

        return view('pages.index', compact('pagesAll', 'statuses'));
    }

    public function create()
    {
        $templates = getThemeTemplates();
        $page = new Post(['type' => 'page']);
        return view('pages.create', compact('templates', 'page'));
    }

    public function store(Request $request)
    {
        // 1) Validate all inputs, including featured_images array
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'nullable|string',
            'slug' => 'nullable|string|unique:posts,slug',
            'status' => 'required|string',
            'template' => 'nullable|string',
            'type' => 'required|string|in:page',

            // ← new featured_images array
            'featured_images' => 'nullable|array',
            'featured_images.*' => 'integer|exists:media,id',

            // SEO
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',

            // custom metas
            'meta_keys' => 'nullable|array',
            'meta_keys.*' => 'required|string|min:1',
            'meta_values' => 'nullable|array',
            'meta_values.*' => 'nullable|string',
        ]);

        // 2) Auto‐generate slug if blank
        if (empty($data['slug'])) {
            $base = Str::slug($data['title']);
            $slug = $base;
            $i = 1;
            while (Post::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $data['slug'] = $slug;
        }

        // 3) Assign featured_images array directly
        $data['featured_images'] = $request->input('featured_images', []);

        // 4) Set the author
        $data['author_id'] = auth()->id();

        // 5) Extract custom meta fields
        $metaData = [];
        if (!empty($data['meta_keys'])) {
            foreach ($data['meta_keys'] as $idx => $key) {
                $val = $data['meta_values'][$idx] ?? '';
                if ($key !== '') {
                    $metaData[] = ['key' => $key, 'value' => $val];
                }
            }
        }

        // 6) Create the page (mass-assign fills featured_images correctly)
        $page = Post::create($data);

        // 7) Save SEO as a proper meta row
        $page->seoMeta()->updateOrCreate(
            ['meta_key' => 'seo'],
            [
                'meta_key' => 'seo',
                'meta_value' => json_encode($data['seo']),
            ]
        );

        // 8) Persist each custom meta
        foreach ($metaData as $m) {
            PostMeta::create([
                'post_id' => $page->id,
                'meta_key' => $m['key'],
                'meta_value' => $m['value'],
            ]);
        }

        return redirect()
            ->route('pages.index')
            ->with('success', 'Page created successfully.');
    }

    public function edit(Post $page)
    {
        $templates = getThemeTemplates();
        return view('pages.edit', compact('page', 'templates'));
    }

    public function update(Request $request, Post $page)
    {
        // 1) Validate (same rules, slug unique ignores current ID)
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'nullable|string',
            'slug' => 'nullable|string|unique:posts,slug,' . $page->id,
            'status' => 'required|string',
            'template' => 'nullable|string',
            'type' => 'required|string|in:page',

            'featured_images' => 'nullable|array',
            'featured_images.*' => 'integer|exists:media,id',

            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',

            'meta_keys' => 'nullable|array',
            'meta_keys.*' => 'required|string|min:1',
            'meta_values' => 'nullable|array',
            'meta_values.*' => 'nullable|string',
        ]);

        // 2) Auto‐slug if blank
        if (empty($data['slug'])) {
            $base = Str::slug($data['title']);
            $slug = $base;
            $i = 1;
            while (Post::where('slug', $slug)->where('id', '!=', $page->id)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $data['slug'] = $slug;
        }

        // 3) Assign updated featured_images
        $data['featured_images'] = $request->input('featured_images', []);

        // 4) Extract custom metas again
        $metaData = [];
        if (!empty($data['meta_keys'])) {
            foreach ($data['meta_keys'] as $idx => $key) {
                $val = $data['meta_values'][$idx] ?? '';
                if ($key !== '') {
                    $metaData[] = ['key' => $key, 'value' => $val];
                }
            }
        }

        // 5) Update the page record
        $page->update($data);

        // 6) Update SEO meta
        $page->seoMeta()->updateOrCreate(
            ['meta_key' => 'seo'],
            [
                'meta_key' => 'seo',
                'meta_value' => json_encode($data['seo']),
            ]
        );

        // 7) Replace old custom metas with new ones
        PostMeta::where('post_id', $page->id)->delete();
        foreach ($metaData as $m) {
            PostMeta::create([
                'post_id' => $page->id,
                'meta_key' => $m['key'],
                'meta_value' => $m['value'],
            ]);
        }

        return redirect()
            ->route('pages.index')
            ->with('success', 'Page updated successfully.');
    }

    public function destroy(Post $page)
    {

        $page->delete();
        return redirect()
            ->route('pages.index')
            ->with('success', 'Page deleted.');
    }

    public function show(string $slug)
    {
        $page = Post::where('slug', $slug)
            ->where('type', 'page')
            ->where('status', 'Published')
            ->firstOrFail();

        $pageOutput = $this->buildPageOutput($page);
        $site = site_settings();
        $themeSettings = theme_settings();
        $theme = getActiveTheme();

        if (
            $slug === ($site->home_page_slug ?? 'home')
            && view()->exists("themes.{$theme}.home")
        ) {
            return view("themes.{$theme}.home", compact('page', 'pageOutput', 'site', 'themeSettings'));
        }

        $view = "themes.{$theme}.templates.{$page->template}";
        if ($page->template && view()->exists($view)) {
            if ($page->template === 'contact') {
                $contact = Contact::first();
                return view($view, compact('page', 'pageOutput', 'site', 'themeSettings', 'contact'));
            }
            return view($view, compact('page', 'pageOutput', 'site', 'themeSettings'));
        }

        return view("themes.{$theme}.page", compact('page', 'pageOutput', 'site', 'themeSettings'));
    }

    private function buildPageOutput(Post $page)
    {
        $json = $page->content ?: '[]';
        return ElementFactory::json2html($json);
    }
}