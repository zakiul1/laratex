<?php

namespace App\Http\Controllers;

use Aponahmed\HtmlBuilder\ElementFactory;
use App\Models\Contact;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\TermTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Show the front‐page (or redirect to a named page slug).
     */
    /*   public function home()
      {
          // If you have a SiteSetting home‐slug, use it; otherwise just list pages
          $settings = SiteSetting::firstOrCreate([]);
          $slug = $settings->home_page_slug ?: 'home';

          // If you want to render a “page” template:
          return $this->show($slug);
      } */



    public function home()
    {
        // 1) Determine which "page" to render
        $settings = SiteSetting::firstOrCreate([]);
        $slug = $settings->home_page_slug ?: 'home';

        // 2) Grab the "Featured Products" category (taxonomy = 'product')
        $featuredCategory = TermTaxonomy::with('term')
            ->where('taxonomy', 'product')
            ->whereHas('term', fn($q) => $q->where('name', 'Featured Products'))
            ->first();

        // dd($featuredCategory);
        // 3) Pull its products (if we found it), eager-loading featuredMedia
        $featuredProducts = $featuredCategory
            ? $featuredCategory
                ->products()
                ->with('featuredMedia')
                ->get()
            : collect();

        // dd($featuredCategory->products);
        // 4) Share both with every view (so your home.blade can use them)
        view()->share(compact('featuredCategory', 'featuredProducts'));

        // 5) Delegate to your normal page-rendering
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
        // 1) Validate, now using featured_media_ids[] from your form
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'nullable|string',
            'slug' => 'nullable|string|unique:posts,slug,' . $page->id,
            'status' => 'required|string',
            'template' => 'nullable|string',
            'type' => 'required|string|in:page',

            // ← changed from featured_images to match your input name
            'featured_media_ids' => 'nullable|array',
            'featured_media_ids.*' => 'integer|exists:media,id',

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
            while (
                Post::where('slug', $slug)
                    ->where('id', '!=', $page->id)
                    ->exists()
            ) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $data['slug'] = $slug;
        }

        // 3) Map your media IDs into the featured_images attribute
        $data['featured_images'] = $data['featured_media_ids'] ?? [];

        // 4) Build metaData from the parallel keys/values arrays
        $metaData = [];
        if (!empty($data['meta_keys'])) {
            foreach ($data['meta_keys'] as $idx => $key) {
                $val = $data['meta_values'][$idx] ?? '';
                if ($key !== '') {
                    $metaData[] = ['key' => $key, 'value' => $val];
                }
            }
        }

        // 5) Update the page—make sure 'featured_images' is fillable on your model
        $page->update([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'slug' => $data['slug'],
            'status' => $data['status'],
            'template' => $data['template'] ?? null,
            'type' => $data['type'],
            'featured_images' => $data['featured_images'],
        ]);

        // 6) Update SEO meta
        $page->seoMeta()->updateOrCreate(
            ['meta_key' => 'seo'],
            ['meta_value' => json_encode($data['seo'])]
        );

        // 7) Replace old custom metas
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
        // 1) Load either a page or a product
        $post = Post::where('slug', $slug)
            ->whereIn('type', ['page', 'product'])
            ->where('status', 'Published')
            ->firstOrFail();

        // 2) Always build the "pageOutput" (HTML from your JSON blocks)
        $pageOutput = $this->buildPageOutput($post);
        $site = site_settings();
        $themeSettings = theme_settings();
        $theme = getActiveTheme();

        // 3) If it’s the home page override, and a "home" view exists:
        if (
            $post->type === 'page'
            && $slug === ($site->home_page_slug ?? 'home')
            && view()->exists("themes.{$theme}.home")
        ) {
            return view(
                "themes.{$theme}.home",
                compact('post', 'pageOutput', 'site', 'themeSettings')
            );
        }

        // 4) If it’s a page, load whatever page‐template is set (or fallback to page.blade.php)
        if ($post->type === 'page') {
            $tpl = $post->template;
            $view = $tpl && view()->exists("themes.{$theme}.templates.{$tpl}")
                ? "themes.{$theme}.templates.{$tpl}"
                : "themes.{$theme}.page";

            // contact gets extra data
            if ($tpl === 'contact') {
                $contact = Contact::first();
                return view($view, compact(
                    'post',
                    'pageOutput',
                    'site',
                    'themeSettings',
                    'contact'
                ));
            }

            return view($view, compact('post', 'pageOutput', 'site', 'themeSettings'));
        }

        // 5) Otherwise it must be a product: render your product template
        //    and pass in the exact same $pageOutput
        return view(
            "themes.{$theme}.templates.product",
            compact('post', 'pageOutput', 'site', 'themeSettings')
        );
    }
    private function buildPageOutput(Post $page)
    {
        $json = $page->content ?: '[]';
        return ElementFactory::json2html($json);
    }
}