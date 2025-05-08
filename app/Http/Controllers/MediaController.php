<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\MediaLibrary;
use App\Models\Media;
use App\Models\Term;
use App\Models\TermTaxonomy;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::with('categories');

        if ($request->filled('search')) {
            $query->where('filename', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->whereHas(
                'categories',
                fn($q) => $q->where('term_taxonomies.term_taxonomy_id', $request->category)
            );
        }

        $perPage = (int) $request->input('per_page', 12);

        $paginated = $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $categories = TermTaxonomy::select('term_taxonomies.*')
            ->join('terms', 'term_taxonomies.term_id', 'terms.id')
            ->where('taxonomy', 'media_category')
            ->orderBy('terms.name', 'asc')
            ->with('term')
            ->get()
            ->map(fn($tax) => (object) [
                'id' => $tax->term_taxonomy_id,
                'name' => $tax->term->name,
                'parent' => $tax->parent,
            ]);

        $shape = function (Media $m) {
            $full = $m->getUrl();
            $thumb = $m->hasGeneratedConversion('thumbnail') ? $m->getUrl('thumbnail') : $full;
            $medium = $m->hasGeneratedConversion('medium') ? $m->getUrl('medium') : $full;
            $large = $m->hasGeneratedConversion('large') ? $m->getUrl('large') : $full;
            [$origWidth] = @getimagesize($m->getPath()) ?: [2048];

            return [
                'id' => $m->id,
                'thumbnail' => $thumb,
                'medium' => $medium,
                'large' => $large,
                'original' => $full,
                'originalWidth' => $origWidth,
                'filename' => $m->filename,
                'categories' => $m->categories->pluck('term_taxonomy_id')->toArray(),
            ];
        };

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'data' => $paginated->getCollection()->map($shape),
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ],
                'categories' => $categories,
            ]);
        }

        $initialMedia = collect($paginated->items())->map($shape);

        return view('media.index', [
            'initialMedia' => $initialMedia,
            'mediaPaginator' => $paginated,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'files.*' => 'required|image|max:5120',
            'category_id' => 'required|integer|exists:term_taxonomies,term_taxonomy_id',
        ], [
            'files.*.image' => 'Each file must be an image.',
            'files.*.max' => 'Each image must not exceed 5MB.',
            'category_id.required' => 'A category is required.',
            'category_id.exists' => 'The selected category is invalid.',
        ]);

        $library = MediaLibrary::firstOrCreate(['id' => 1]);
        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $mediaItem = $library
                ->addMedia($file)
                ->usingFileName(time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension())
                ->toMediaCollection('library');

            $mediaItem->categories()
                ->sync([$request->category_id => ['object_type' => 'media']]);

            $uploaded[] = [
                'id' => $mediaItem->id,
                'url' => $mediaItem->getUrl(),
                'thumbnail' => $mediaItem->getUrl('thumbnail'),
                'filename' => $mediaItem->name,
            ];
        }

        return response()->json(['uploaded' => $uploaded], 201);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent' => 'nullable|integer',
        ], [
            'name.required' => 'The category name is required.',
            'name.max' => 'The category name must not exceed 255 characters.',
        ]);

        $parentId = $validated['parent'] ?? 0;

        if ($parentId > 0 && !TermTaxonomy::where('term_taxonomy_id', $parentId)->where('taxonomy', 'media_category')->exists()) {
            return response()->json(['error' => 'The selected parent category is invalid.'], 422);
        }

        $term = Term::firstOrCreate(
            ['slug' => Str::slug($validated['name'])],
            ['name' => $validated['name']]
        );

        $tax = TermTaxonomy::firstOrCreate(
            ['term_id' => $term->id, 'taxonomy' => 'media_category'],
            ['parent' => $parentId, 'description' => '', 'count' => 0]
        );

        return response()->json([
            'id' => $tax->term_taxonomy_id,
            'name' => $term->name,
            'parent' => $tax->parent,
        ], 201);
    }

    public function destroy(Media $media)
    {
        $media->categories()->detach();
        $media->delete();
        return response()->json(['deleted' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'No IDs provided'], 422);
        }

        Media::whereIn('id', $ids)->get()->each(function (Media $m) {
            $m->categories()->detach();
            $m->delete();
        });

        return response()->json(['deleted' => true]);
    }


    /**
     * Return JSON for the browser.
     */
    public function browserIndex(Request $request)
    {
        $query = Media::query();

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('mime_type', 'like', $request->type . '/%');
        }
        if ($request->filled('search')) {
            $query->where('filename', 'like', '%' . $request->search . '%');
        }

        $perPage = 20;
        $paginated = $query->latest()->paginate($perPage);

        $data = $paginated
            ->getCollection()
            ->map(fn(Media $m) => [
                'id' => $m->id,
                'url' => $m->getUrl(),
            ]);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

}