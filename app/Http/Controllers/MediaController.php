<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\MediaLibrary;           // the “library” owner for your Spatie media
use App\Models\Media;                  // your Media model (backed by `media` table)
use App\Models\Term;
use App\Models\TermTaxonomy;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        // 1) Build base query
        $query = Media::with('categories');

        // 2) Search filter
        if ($request->filled('search')) {
            $query->where('filename', 'like', '%' . $request->search . '%');
        }

        // 3) Category filter
        if ($request->filled('category')) {
            $query->whereHas(
                'categories',
                fn($q) => $q->where('term_taxonomies.term_taxonomy_id', $request->category)
            );
        }

        // 4) Per-page (default to 12)
        $perPage = (int) $request->input('per_page', 12);

        // 5) Paginate and preserve query string
        $paginated = $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // 6) Load categories for the dropdown
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

        // helper to shape each Media item
        $shape = function (Media $m) {
            $fullUrl = $m->getUrl();
            $thumbUrl = $m->hasGeneratedConversion('thumbnail') ? $m->getUrl('thumbnail') : $fullUrl;
            $mediumUrl = $m->hasGeneratedConversion('medium') ? $m->getUrl('medium') : $fullUrl;
            $largeUrl = $m->hasGeneratedConversion('large') ? $m->getUrl('large') : $fullUrl;

            // determine original image width
            $path = $m->getPath();
            $info = @getimagesize($path);
            $origWidth = $info ? $info[0] : 2048;

            return [
                'id' => $m->id,
                'thumbnail' => $thumbUrl,
                'medium' => $mediumUrl,
                'large' => $largeUrl,
                'original' => $fullUrl,
                'originalWidth' => $origWidth,
                'filename' => $m->filename,
                'categories' => $m->categories->pluck('term_taxonomy_id')->toArray(),
            ];
        };

        // 7) JSON / AJAX response
        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            $data = $paginated->getCollection()->map($shape);

            return response()->json([
                'data' => $data,
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ],
                'categories' => $categories,
            ]);
        }

        // 8) Initial Blade render: same shape
        $initialMedia = collect($paginated->items())->map($shape);

        // 9) Render view
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
            'category_id' => 'nullable|integer|exists:term_taxonomies,term_taxonomy_id',
        ]);

        $catId = $request->input('category_id') ?: $this->getOrCreateUncategorized();
        $uploaded = [];
        $library = MediaLibrary::firstOrCreate(['id' => 1]);

        foreach ($request->file('files') as $file) {
            $mediaItem = $library
                ->addMedia($file)
                ->usingFileName(
                    time() . '_' . Str::slug(pathinfo(
                        $file->getClientOriginalName(),
                        PATHINFO_FILENAME
                    )) . '.' . $file->getClientOriginalExtension()
                )
                ->toMediaCollection('library');

            $mediaItem->categories()
                ->sync([$catId => ['object_type' => 'media']]);

            $uploaded[] = [
                'id' => $mediaItem->id,
                'url' => $mediaItem->getUrl(),
                'thumbnail' => $mediaItem->getUrl('thumbnail'),
                'filename' => $mediaItem->name,
            ];
        }

        return response()->json(['uploaded' => $uploaded], 201);
    }

    /**
     * Create a new media category.
     */
    public function storeCategory(Request $request)
    {
        // First, validate name and that parent—if present—is at least an integer.
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent' => 'nullable|integer',
        ]);

        // Normalize parent to 0 if it wasn't given
        $parentId = $validated['parent'] ?? 0;

        // If they did supply a real parent (>0), make sure it exists in media_category
        if ($parentId > 0) {
            $exists = TermTaxonomy::where('term_taxonomy_id', $parentId)
                ->where('taxonomy', 'media_category')
                ->exists();

            if (!$exists) {
                return response()->json([
                    'error' => 'The selected parent is invalid.'
                ], 422);
            }
        }

        // Prevent duplicate names in this taxonomy
        $existingTerm = Term::where('name', $validated['name'])->first();
        if ($existingTerm) {
            $existingTax = TermTaxonomy::where('term_id', $existingTerm->id)
                ->where('taxonomy', 'media_category')
                ->first();

            if ($existingTax) {
                return response()->json([
                    'error' => 'A category with this name already exists'
                ], 422);
            }
        }

        // Create the Term
        $term = Term::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        // Create its taxonomy entry
        $tax = TermTaxonomy::create([
            'term_id' => $term->id,
            'taxonomy' => 'media_category',
            'description' => '',
            'parent' => $parentId,
            'count' => 0,
        ]);

        return response()->json([
            'id' => $tax->term_taxonomy_id,
            'name' => $term->name,
            'parent' => $tax->parent,
        ], 201);
    }

    protected function getOrCreateUncategorized(): int
    {
        $term = Term::firstOrCreate(
            ['slug' => 'uncategorized'],
            ['name' => 'Uncategorized']
        );
        $tax = TermTaxonomy::firstOrCreate(
            ['term_id' => $term->id, 'taxonomy' => 'media_category'],
            ['parent' => 0, 'description' => '', 'count' => 0]
        );
        return $tax->term_taxonomy_id;
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
            return response()->json(['error' => 'No media IDs provided'], 422);
        }
        Media::whereIn('id', $ids)->get()->each(fn(Media $m) => [
            $m->categories()->detach(),
            $m->delete(),
        ]);
        return response()->json(['deleted' => true]);
    }
}