<?php

namespace App\Http\Controllers;

use Aponahmed\HtmlBuilder\ElementFactory;
use App\Models\Contact;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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


    public function show($slug)
    {
        $page = Post::where('slug', $slug)
            ->where('type', 'page')
            ->where('status', 'Published')
            ->firstOrFail();

        $theme = getActiveTheme();
        $templateView = "themes.{$theme}.templates.{$page->template}";
        $defaultView = "themes.{$theme}.page";
        $pageOutput = $this->buildPageOutput($page);
        // dd($pageOutput);
        $site = site_settings();
        $themeSettings = theme_settings();

        // if “contact” template and view exists
        if ($page->template === 'contact' && view()->exists($templateView)) {
            $contact = Contact::first();
            return view($templateView, compact('page', 'site', 'themeSettings', 'contact'));
        }

        // if custom template exists
        if ($page->template && view()->exists($templateView)) {
            return view($templateView, compact('page', 'site', 'themeSettings', 'pageOutput'));
        }

        // fallback
        return view($defaultView, compact('page', 'site', 'themeSettings', 'pageOutput'));
    }

    /**
     * Decode your block‐builder JSON (stored in `block`),  
     * or default to an empty array if it’s missing or invalid.
     */
    private function buildPageOutput(Post $page)
    {
        // ← switch to the 'block' field, not `content`
        //dd($page->content);
        $json = $page->content;

        // if it’s null, non‐string, or empty, force “[]”
        if (!is_string($json) || trim($json) === '') {
            $json = '[]';
        }

        return ElementFactory::json2html($json);
    }
}