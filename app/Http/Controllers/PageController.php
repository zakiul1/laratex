<?php

namespace App\Http\Controllers;

use Aponahmed\HtmlBuilder\ElementFactory;
use App\Models\Contact;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\SiteSetting;

class PageController extends Controller
{
    public function index()
    {
        // Grab *all* pages (for client‐side table)
        $pagesAll = Post::where('type', 'page')
            ->select('id', 'slug', 'title', 'status', 'template', 'featured_image', 'created_at')
            ->latest()
            ->get();

        // Options for the “Status” filter
        $statuses = [
            'published' => 'Published',
            'draft' => 'Draft',
        ];

        // Pass into the view for Alpine
        return view('pages.index', compact('pagesAll', 'statuses'));
    }

    public function home()
    {
        // 1) Ensure we have a settings record
        $settings = SiteSetting::firstOrCreate([]);

        // 2) Use the admin’s chosen slug, or fall back to 'home'
        $slug = $settings->home_page_slug ?: 'home';

        // 3) Delegate everything (page lookup, block‐builder rendering,
        //    template resolution) to your `show()` method
        return $this->show($slug);
    }

    public function create()
    {
        $templates = getThemeTemplates(); // ✅ from theme.json
        $initialImage = '';
        return view('pages.create', compact('templates', 'initialImage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'nullable|string',
            'slug' => 'nullable|string|unique:posts,slug',
            'status' => 'required|string',
            'template' => 'nullable|string',
            'featured_image' => 'nullable|image',
            'type' => 'required|string|in:page',
        ]);

        if (!$data['slug']) {
            $baseSlug = Str::slug($data['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (Post::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('pages', 'public');
        }

        $metaData = [];
        if ($request->has('meta_keys')) {
            foreach ($request->meta_keys as $index => $key) {
                if ($key !== null && $key !== '') {
                    $metaData[] = [
                        'key' => $key,
                        'value' => $request->meta_values[$index] ?? ''
                    ];
                }
            }
        }

        $data['metas'] = $metaData;
        $data['author_id'] = auth()->id();

        Post::create($data);

        return redirect()->route('pages.index')->with('success', 'Page created successfully.');
    }

    public function edit(Post $page)
    {
        $templates = getThemeTemplates(); // ✅ from theme.json
        $initialImage = $page->featured_image ? asset('storage/' . $page->featured_image) : '';
        return view('pages.edit', compact('page', 'templates', 'initialImage'));
    }

    public function update(Request $request, Post $page)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'nullable|string',
            'slug' => 'nullable|string|unique:posts,slug,' . $page->id,
            'status' => 'required|string',
            'template' => 'nullable|string',
            'featured_image' => 'nullable|image',
            'type' => 'required|string|in:page',
        ]);

        if (!$data['slug']) {
            $baseSlug = Str::slug($data['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (Post::where('slug', $slug)->where('id', '!=', $page->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('featured_image')) {
            if ($page->featured_image) {
                File::delete(public_path('storage/' . $page->featured_image));
            }
            $data['featured_image'] = $request->file('featured_image')->store('pages', 'public');
        }

        $metaData = [];
        if ($request->has('meta_keys')) {
            foreach ($request->meta_keys as $index => $key) {
                if ($key !== null && $key !== '') {
                    $metaData[] = [
                        'key' => $key,
                        'value' => $request->meta_values[$index] ?? ''
                    ];
                }
            }
        }
        $data['metas'] = $metaData;

        $page->update($data);

        return redirect()->route('pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Post $page)
    {
        if ($page->featured_image) {
            File::delete(public_path('storage/' . $page->featured_image));
        }

        $page->delete();

        return redirect()->route('pages.index')->with('success', 'Page deleted successfully.');
    }


    public function show(string $slug)
    {
        // 1) Fetch the page record
        $page = Post::where('slug', $slug)
            ->where('type', 'page')
            ->where('status', 'Published')
            ->firstOrFail();

        // 2) Build your block-builder or shortcode output
        $pageOutput = $this->buildPageOutput($page);

        // 3) Site-wide settings and theme settings
        $site = site_settings();
        $themeSettings = theme_settings();
        $theme = getActiveTheme();

        // 4) If this is the front-page slug and your theme has a special home view...
        if (
            $slug === ($site->home_page_slug ?? 'home')
            && view()->exists("themes.{$theme}.home")
        ) {
            return view(
                "themes.{$theme}.home",
                compact('page', 'pageOutput', 'site', 'themeSettings')
            );
        }

        // 5) Otherwise, check for a custom template view...
        $templateView = "themes.{$theme}.templates.{$page->template}";
        if ($page->template && view()->exists($templateView)) {
            // Contact template gets the Contact model injected
            if ($page->template === 'contact') {
                $contact = Contact::first();
                return view(
                    $templateView,
                    compact('page', 'pageOutput', 'site', 'themeSettings', 'contact')
                );
            }
            return view(
                $templateView,
                compact('page', 'pageOutput', 'site', 'themeSettings')
            );
        }

        // 6) Fallback to your generic theme page view
        $defaultView = "themes.{$theme}.page";
        return view(
            $defaultView,
            compact('page', 'pageOutput', 'site', 'themeSettings')
        );
    }

    /**
     * Turn your JSON/block-builder data into HTML
     */
    private function buildPageOutput(Post $page)
    {
        $json = $page->content;
        if (!is_string($json) || trim($json) === '') {
            $json = '[]';
        }
        return ElementFactory::json2html($json);
    }
}