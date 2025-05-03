<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Models\TermTaxonomy;
use App\Models\TermTaxonomyImage;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductTaxonomyController extends Controller
{
    public function index()
    {
        $taxonomies = TermTaxonomy::with(['term', 'parentTaxonomy', 'images.media'])
            ->where('taxonomy', 'product')
            ->paginate(20);

        return view('admin.product-taxonomies.index', compact('taxonomies'));
    }

    public function create()
    {
        $parents = TermTaxonomy::with('term')
            ->where('taxonomy', 'product')
            ->get();

        $initialImages = []; // no images on create

        return view('admin.product-taxonomies.form', compact(
            'parents',
            'initialImages'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', Rule::unique('terms', 'slug')],
            'description' => 'nullable|string',
            'parent' => ['nullable', 'integer', 'exists:term_taxonomies,term_taxonomy_id'],
            'status' => 'required|boolean',
            'image_ids' => 'nullable|array',
            'image_ids.*' => 'exists:media,id',
        ]);

        // ensure parent is numeric and default to 0
        $parent = intval($data['parent'] ?? 0);

        // generate a unique slug if needed
        $slug = $data['slug'] ?: Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (Term::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        // Create Term
        $term = Term::firstOrCreate(
            ['slug' => $slug],
            ['name' => $data['name']]
        );

        // Create Taxonomy
        $tt = TermTaxonomy::create([
            'term_id' => $term->id,
            'taxonomy' => 'product',
            'description' => $data['description'] ?? null,
            'parent' => $parent,
            'count' => 0,
            'status' => $data['status'],
        ]);

        // Attach images by media_id
        foreach ($data['image_ids'] ?? [] as $mid) {
            if (Media::where('id', $mid)->exists()) {
                TermTaxonomyImage::create([
                    'term_taxonomy_id' => $tt->term_taxonomy_id,
                    'media_id' => $mid,
                ]);
            }
        }

        return redirect()
            ->route('product-taxonomies.index')
            ->with('success', 'Category created.');
    }

    public function edit(int $id)
    {
        $taxonomy = TermTaxonomy::with(['term', 'parentTaxonomy', 'images.media'])
            ->where('taxonomy', 'product')
            ->findOrFail($id);

        $parents = TermTaxonomy::with('term')
            ->where('taxonomy', 'product')
            ->where('term_taxonomy_id', '!=', $id)
            ->get();

        $initialImages = $taxonomy->images
            ->filter(fn($ti) => $ti->media)
            ->map(fn($ti) => [
                'id' => $ti->media->id,
                'url' => $ti->getUrl('thumbnail'),
            ])
            ->values()
            ->all();

        return view('admin.product-taxonomies.form', compact(
            'taxonomy',
            'parents',
            'initialImages'
        ));
    }

    public function update(Request $request, int $id)
    {
        $tt = TermTaxonomy::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', Rule::unique('terms', 'slug')->ignore($tt->term_id)],
            'description' => 'nullable|string',
            'parent' => ['nullable', 'integer', 'exists:term_taxonomies,term_taxonomy_id'],
            'status' => 'required|boolean',
            'image_ids' => 'nullable|array',
            'image_ids.*' => 'exists:media,id',
        ]);

        $parent = intval($data['parent'] ?? 0);

        // update the underlying Term
        $tt->term->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
        ]);

        // update Taxonomy
        $tt->update([
            'description' => $data['description'] ?? null,
            'parent' => $parent,
            'status' => $data['status'],
        ]);

        // reset images
        $tt->images()->delete();
        foreach ($data['image_ids'] ?? [] as $mid) {
            if (Media::where('id', $mid)->exists()) {
                TermTaxonomyImage::create([
                    'term_taxonomy_id' => $tt->term_taxonomy_id,
                    'media_id' => $mid,
                ]);
            }
        }

        return redirect()
            ->route('product-taxonomies.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(int $id)
    {
        $tt = TermTaxonomy::findOrFail($id);

        // cascade delete images & term
        $tt->images()->delete();
        $tt->term()->delete();
        $tt->delete();

        return redirect()
            ->route('product-taxonomies.index')
            ->with('success', 'Category deleted.');
    }



}