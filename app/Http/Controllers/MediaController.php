<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Term;
use App\Models\TermTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;  // ← Add this import

class MediaController extends Controller
{
    /**
     * Display media library page.
     */
    public function index()
    {
        $items = Media::all();

        $mediaCategories = TermTaxonomy::with('term')
            ->where('taxonomy', 'media_category')
            ->get();

        return view('media.index', compact('items', 'mediaCategories'));
    }

    /**
     * JSON endpoint for modal or API.
     */
    public function json(Request $request)
    {
        $media = Media::with('categories.term')
            ->orderBy('created_at', 'desc')
            ->get();

        $payload = $media->map(function (Media $m) {
            return [
                'id' => $m->id,
                'url' => Storage::url($m->path),
                'filename' => $m->filename,
                'mime_type' => $m->mime_type,
                'size' => $m->size,
                'categories' => $m->categories->pluck('term_id')->all(),
            ];
        });

        return response()->json($payload);
    }

    /**
     * Handle upload(s) and category sync.
     */
    public function store(Request $request)
    {
        // Single-file upload (modal)
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|image|max:5120',
                'categories' => 'array',
                'categories.*' => 'integer|exists:term_taxonomy,term_taxonomy_id',
            ]);

            $file = $request->file('file');
            $path = $file->store('media', 'public');

            $media = Media::create([
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $media->categories()->sync($request->input('categories', []));

            return response()->json([
                'id' => $media->id,
                'url' => Storage::url($path),
                'categories' => $media->categories->pluck('term_id'),
            ], 201);
        }

        // Bulk-file upload (standalone page)
        $request->validate([
            'files.*' => 'required|image|max:5120',
            'categories' => 'array',
            'categories.*' => 'integer|exists:term_taxonomy,term_taxonomy_id',
        ]);

        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $path = $file->store('media', 'public');
            $media = Media::create([
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $media->categories()->sync($request->input('categories', []));

            $uploaded[] = [
                'id' => $media->id,
                'url' => Storage::url($path),
                'filename' => $media->filename,
                'categories' => $media->categories->pluck('term_id'),
            ];
        }

        return response()->json(['uploaded' => $uploaded], 201);
    }

    /**
     * Delete a single media.
     */
    public function destroy(Media $media)
    {
        Storage::disk('public')->delete($media->path);
        $media->categories()->detach();
        $media->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * Bulk delete media items.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['deleted' => false], 422);
        }

        $medias = Media::whereIn('id', $ids)->get();
        foreach ($medias as $media) {
            Storage::disk('public')->delete($media->path);
            $media->categories()->detach();
            $media->delete();
        }

        return response()->json(['deleted' => true]);
    }

    /**
     * AJAX: Create a new media category.
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // create term
        $term = Term::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),  // ← Uses Str
        ]);

        // create taxonomy row
        $taxonomy = TermTaxonomy::create([
            'term_id' => $term->id,
            'taxonomy' => 'media_category',
            'description' => '',
            'parent' => 0,    // default no parent
            'count' => 0,
        ]);

        return response()->json([
            'term_taxonomy_id' => $taxonomy->term_taxonomy_id,
            'term' => ['name' => $term->name],
            'parent' => $taxonomy->parent,
        ], 201);
    }

    /**
     * AJAX: Delete a media category.
     */
    public function destroyCategory($id)
    {
        TermTaxonomy::where('term_taxonomy_id', $id)->delete();
        return response()->json(['deleted' => true]);
    }
}