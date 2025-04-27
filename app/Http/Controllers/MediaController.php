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
    public function index()
    {
        $items = Media::all();
        $mediaData = $items->map(fn($m) => [
            'id'         => $m->id,
            'url'        => Storage::url($m->path),
            'filename'   => $m->filename,
            // <-- add this line so Alpine can filter by category:
            'categories' => $m->categories->pluck('term_taxonomy_id')->toArray(),
        ])->toArray();

        $categories = TermTaxonomy::with('term')
            ->where('taxonomy', 'media_category')
            ->get()
            ->map(fn($tax) => [
                'id'     => $tax->term_taxonomy_id,
                'name'   => $tax->term->name,
                'parent' => $tax->parent,
            ])->toArray();

        return view('media.index', compact('mediaData', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files.*'     => 'required|image|max:5120',
            'category_id' => 'nullable|integer',
        ]);

        // if no category chosen, ensure “Uncategorized” exists and use it
        $catId = $request->input('category_id');
        if (! $catId) {
            $term = Term::firstOrCreate(
                ['slug' => 'uncategorized'],
                ['name' => 'Uncategorized']
            );

            $taxonomy = TermTaxonomy::firstOrCreate(
                ['term_id' => $term->id, 'taxonomy' => 'media_category'],
                ['parent' => 0, 'description' => '', 'count' => 0]
            );

            $catId = $taxonomy->term_taxonomy_id;
        }

        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $path = $file->store('media', 'public');

            $media = Media::create([
                'filename'  => $file->getClientOriginalName(),
                'path'      => $path,
                'mime_type' => $file->getMimeType(),
                'size'      => $file->getSize(),
            ]);

            $media->categories()->sync([$catId]);

            $uploaded[] = [
                'id'       => $media->id,
                'url'      => Storage::url($path),
                'filename' => $media->filename,
            ];
        }

        return response()->json(['uploaded' => $uploaded], 201);
    }

    public function destroy(Media $media)
    {
        Storage::disk('public')->delete($media->path);
        $media->categories()->detach();
        $media->delete();

        return response()->json(['deleted' => true]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'parent' => 'nullable|integer',
        ]);

        $term = Term::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        $taxonomy = TermTaxonomy::create([
            'term_id'    => $term->id,
            'taxonomy'   => 'media_category',
            'description'=> '',
            'parent'     => $request->parent ?: 0,
            'count'      => 0,
        ]);

        return response()->json([
            'id'     => $taxonomy->term_taxonomy_id,
            'name'   => $term->name,
            'parent' => $taxonomy->parent,
        ], 201);
    }

    public function destroyCategory($id)
    {
        TermTaxonomy::where('term_taxonomy_id', $id)->delete();
        return response()->json(['deleted' => true]);
    }
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['deleted' => false], 422);
        }

        Media::whereIn('id', $ids)
            ->get()
            ->each(function (Media $media) {
                // delete file from storage
                Storage::disk('public')->delete($media->path);
                // detach any pivot categories
                $media->categories()->detach();
                // remove the record
                $media->delete();
            });

        return response()->json(['deleted' => true]);
    }
}
