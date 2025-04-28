<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Term;
use App\Models\TermTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Display a paginated, searchable, filterable media library.
     */
    public function index(Request $request)
    {
        try {
            // 1) Build base query
            $query = Media::with('categories');

            // 2) Search filter
            if ($request->filled('search')) {
                $query->where('filename', 'like', '%' . $request->search . '%');
            }

            // 3) Category filter
            if ($request->filled('category')) {
                $cat = $request->category;
                $query->whereHas('categories', fn($q) => $q->where('term_taxonomies.term_taxonomy_id', $cat));
            }

            // 4) Per-page
            $perPage = intval($request->input('per_page', 20));

            // 5) Paginate
            $paginated = $query
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();

            // 6) If this is an AJAX / JSON request, return JSON
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                // Transform each item into the shape Alpine expects
                $data = $paginated->getCollection()
                    ->map(fn($m) => [
                        'id' => $m->id,
                        'url' => Storage::url($m->path),
                        'filename' => $m->filename,
                        'categories' => $m->categories->pluck('term_taxonomy_id')->toArray(),
                    ]);

                return response()->json([
                    'data' => $data,
                    'meta' => [
                        'current_page' => $paginated->currentPage(),
                        'last_page' => $paginated->lastPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                    ],
                    'categories' => TermTaxonomy::select('term_taxonomies.*')
                        ->join('terms', 'term_taxonomies.term_id', '=', 'terms.id')
                        ->where('taxonomy', 'media_category')
                        ->orderBy('terms.name', 'asc')
                        ->with('term')
                        ->get()
                        ->map(fn($tax) => (object) [
                            'id' => $tax->term_taxonomy_id,
                            'name' => $tax->term->name,
                            'parent' => $tax->parent,
                        ]),
                ]);
            }

            // 7) Otherwise, render your full HTML view
            return view('media.index', [
                'media' => $paginated,
                'categories' => TermTaxonomy::select('term_taxonomies.*')
                    ->join('terms', 'term_taxonomies.term_id', '=', 'terms.id')
                    ->where('taxonomy', 'media_category')
                    ->orderBy('terms.name', 'asc')
                    ->with('term')
                    ->get()
                    ->map(fn($tax) => (object) [
                        'id' => $tax->term_taxonomy_id,
                        'name' => $tax->term->name,
                        'parent' => $tax->parent,
                    ]),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading media: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['error' => 'Failed to load media: ' . $e->getMessage()], 500);
            }
            throw $e; // For non-AJAX requests, rethrow to see the error in the browser
        }
    }

    /**
     * Handle new uploads (files[] + optional category_id).
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'files.*' => 'required|image|max:5120',
                'category_id' => 'nullable|integer|exists:term_taxonomies,term_taxonomy_id',
            ]);

            // Ensure “Uncategorized” exists when no category is passed
            $catId = $request->input('category_id');
            if (!$catId) {
                $term = Term::firstOrCreate(
                    ['slug' => 'uncategorized'],
                    ['name' => 'Uncategorized']
                );

                $tax = TermTaxonomy::firstOrCreate(
                    ['term_id' => $term->id, 'taxonomy' => 'media_category'],
                    ['parent' => 0, 'description' => '', 'count' => 0]
                );

                $catId = $tax->term_taxonomy_id;
            }

            $uploaded = [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('media', 'public');

                $media = Media::create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);

                // Sync the category with the correct object_type
                $media->categories()->sync([$catId => ['object_type' => 'media']]);
                \Log::info('Media created and associated with category', [
                    'media_id' => $media->id,
                    'category_id' => $catId,
                    'object_type' => 'media',
                ]);

                $uploaded[] = [
                    'id' => $media->id,
                    'url' => Storage::url($path),
                    'filename' => $media->filename,
                ];
            }

            return response()->json(['uploaded' => $uploaded], 201);
        } catch (\Exception $e) {
            \Log::error('Media upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to upload media: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a single media item (and its file + pivot links).
     */
    public function destroy(Media $media)
    {
        try {
            Storage::disk('public')->delete($media->path);
            $media->categories()->detach();
            $media->delete();

            return response()->json(['deleted' => true]);
        } catch (\Exception $e) {
            \Log::error('Media deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete media: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bulk-delete multiple media items at once.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (!is_array($ids) || empty($ids)) {
                return response()->json(['error' => 'No media IDs provided'], 422);
            }

            Media::whereIn('id', $ids)->get()->each(function (Media $m) {
                Storage::disk('public')->delete($m->path);
                $m->categories()->detach();
                $m->delete();
            });

            return response()->json(['deleted' => true]);
        } catch (\Exception $e) {
            \Log::error('Bulk media deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to bulk delete media: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new media category.
     */
    public function storeCategory(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'parent' => 'nullable|integer',
            ]);

            // Check for duplicate category name
            $existingTerm = Term::where('name', $validated['name'])->first();
            if ($existingTerm) {
                $existingTaxonomy = TermTaxonomy::where('term_id', $existingTerm->id)
                    ->where('taxonomy', 'media_category')
                    ->first();
                if ($existingTaxonomy) {
                    return response()->json(['error' => 'A category with this name already exists'], 422);
                }
            }

            // Check if parent exists (excluding 0, which is the default for no parent)
            if ($validated['parent'] !== 0) {
                $parentExists = TermTaxonomy::where('term_taxonomy_id', $validated['parent'])->exists();
                if (!$parentExists) {
                    return response()->json(['error' => 'Parent category does not exist'], 422);
                }
            }

            // Begin a transaction to ensure atomicity
            \DB::beginTransaction();

            $term = Term::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
            ]);

            $tax = TermTaxonomy::create([
                'term_id' => $term->id,
                'taxonomy' => 'media_category',
                'description' => '',
                'parent' => $request->parent ?: 0,
                'count' => 0,
            ]);

            \DB::commit();

            return response()->json([
                'id' => $tax->term_taxonomy_id,
                'name' => $term->name,
                'parent' => $tax->parent,
            ], 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Category creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a category by its taxonomy ID.
     */
    public function destroyCategory($id)
    {
        try {
            $tax = TermTaxonomy::where('term_taxonomy_id', $id)->firstOrFail();
            $term = Term::findOrFail($tax->term_id);

            \DB::beginTransaction();
            $tax->delete();
            $term->delete();
            \DB::commit();

            return response()->json(['deleted' => true]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Category deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete category: ' . $e->getMessage()], 500);
        }
    }
}