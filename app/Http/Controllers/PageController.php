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
        $pagesAll = Post::where('type', 'page')
            ->select('id', 'slug', 'title', 'status', 'template', 'featured_image', 'created_at')
            ->latest()
            ->get();

        $statuses = [
            'published' => 'Published',
            'draft' => 'Draft',
        ];

        return view('pages.index', compact('pagesAll', 'statuses'));
    }

    public function home()
    {
        $settings = SiteSetting::firstOrCreate([]);
        $slug = $settings->home_page_slug ?: 'home';

        return $this->show($slug);
    }

    public function create()
    {
        $templates = getThemeTemplates();
        $initialImage = '';

        // pass in an empty Post so your SEO partial can do $page->seoMeta->meta
        $page = new Post(['type' => 'page']);

        return view('pages.create', compact('templates', 'initialImage', 'page'));
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

            // ─── SEO fields ────────────────────────────────────────────────
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
        ]);

        // ─── slug auto-gen if blank ────────────────────────────────────
        if (!$data['slug']) {
            $baseSlug = Str::slug($data['title']);
            $slug = $baseSlug;
            $i = 1;
            while (Post::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        // ─── featured image ───────────────────────────────────────────
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request
                ->file('featured_image')
                ->store('pages', 'public');
        }

        // ─── your existing custom metas logic ────────────────────────
        $metaData = [];
        if ($request->has('meta_keys')) {
            foreach ($request->meta_keys as $idx => $key) {
                if ($key !== null && $key !== '') {
                    $metaData[] = [
                        'key' => $key,
                        'value' => $request->meta_values[$idx] ?? '',
                    ];
                }
            }
        }
        $data['metas'] = $metaData;
        $data['author_id'] = auth()->id();

        // ─── create the page ──────────────────────────────────────────
        $page = Post::create($data);

        // ─── sync SEO into seo_meta table ────────────────────────────
        $page->seoMeta()
            ->updateOrCreate(
                [],
                ['meta' => $data['seo']]
            );

        return redirect()
            ->route('pages.index')
            ->with('success', 'Page created successfully.');
    }

    public function edit(Post $page)
    {
        $templates = getThemeTemplates();
        $initialImage = $page->featured_image
            ? asset('storage/' . $page->featured_image)
            : '';

        // $page is already loaded by route‐model binding:
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

            // ─── SEO fields ────────────────────────────────────────────────
            'seo.title' => 'nullable|string|max:255',
            'seo.robots' => 'nullable|string|in:Index & Follow,NoIndex & Follow,NoIndex & NoFollow,No Archive,No Snippet',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
        ]);

        if (!$data['slug']) {
            $baseSlug = Str::slug($data['title']);
            $slug = $baseSlug;
            $i = 1;
            while (
                Post::where('slug', $slug)
                    ->where('id', '!=', $page->id)
                    ->exists()
            ) {
                $slug = $baseSlug . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('featured_image')) {
            File::delete(public_path('storage/' . $page->featured_image));
            $data['featured_image'] = $request
                ->file('featured_image')
                ->store('pages', 'public');
        }

        // preserve your existing metas array
        $metaData = [];
        if ($request->has('meta_keys')) {
            foreach ($request->meta_keys as $idx => $key) {
                if ($key !== null && $key !== '') {
                    $metaData[] = [
                        'key' => $key,
                        'value' => $request->meta_values[$idx] ?? '',
                    ];
                }
            }
        }
        $data['metas'] = $metaData;

        $page->update($data);

        // ─── update SEO polymorphic JSON ──────────────────────────────
        $page->seoMeta()
            ->updateOrCreate(
                [],
                ['meta' => $data['seo']]
            );

        return redirect()
            ->route('pages.index')
            ->with('success', 'Page updated successfully.');
    }

    public function destroy(Post $page)
    {
        if ($page->featured_image) {
            File::delete(public_path('storage/' . $page->featured_image));
        }
        $page->delete();

        return redirect()
            ->route('pages.index')
            ->with('success', 'Page deleted successfully.');
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

        // front‐page override…
        if (
            $slug === ($site->home_page_slug ?? 'home')
            && view()->exists("themes.{$theme}.home")
        ) {
            return view(
                "themes.{$theme}.home",
                compact('page', 'pageOutput', 'site', 'themeSettings')
            );
        }

        // custom template
        $templateView = "themes.{$theme}.templates.{$page->template}";
        if ($page->template && view()->exists($templateView)) {
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

        // default
        return view(
            "themes.{$theme}.page",
            compact('page', 'pageOutput', 'site', 'themeSettings')
        );
    }

    private function buildPageOutput(Post $page)
    {
        $json = $page->content ?: '[]';
        return ElementFactory::json2html($json);
    }
}