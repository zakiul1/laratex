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
        $taxonomies = TermTaxonomy::with(['term', 'parentTaxonomy', 'images'])
            ->where('taxonomy', 'product')
            ->paginate(20);

        return view('admin.product-taxonomies.index', compact('taxonomies'));
    }

    public function create()
    {
        $parents = TermTaxonomy::with('term')
            ->where('taxonomy', 'product')
            ->get();

        return view('admin.product-taxonomies.create', [
            'parents' => $parents,
            'taxonomy' => null,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->input('parent') === '' || $request->input('parent') === '0') {
            $request->merge(['parent' => null]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', Rule::unique('terms', 'slug')],
            'description' => 'nullable|string',
            'parent' => 'nullable|exists:term_taxonomies,term_taxonomy_id',
            'status' => 'required|boolean',
            'image_ids' => 'nullable|array',
            'image_ids.*' => 'exists:media,id',
        ]);

        // ensure unique slug
        $slug = $data['slug'] ?? Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (Term::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        $term = Term::firstOrCreate(
            ['slug' => $slug],
            ['name' => $data['name']]
        );

        $tt = TermTaxonomy::create([
            'term_id' => $term->id,
            'taxonomy' => 'product',
            'description' => $data['description'] ?? null,
            'parent' => $data['parent'] ?? 0,
            'count' => 0,
            'status' => $data['status'],
        ]);

        if (!empty($data['image_ids'])) {
            foreach ($data['image_ids'] as $mid) {
                if ($media = Media::find($mid)) {
                    TermTaxonomyImage::create([
                        'term_taxonomy_id' => $tt->term_taxonomy_id,
                        'path' => $media->path,
                    ]);
                }
            }
        }

        return redirect()
            ->route('product-taxonomies.index')
            ->with('success', 'Product category created.');
    }

    public function edit($id)
    {
        $taxonomy = TermTaxonomy::with(['term', 'images'])
            ->findOrFail($id);

        if (!$taxonomy->term) {
            return redirect()
                ->route('product-taxonomies.index')
                ->with('error', 'The taxonomy term is missing or invalid.');
        }

        $parents = TermTaxonomy::with('term')
            ->where('taxonomy', 'product')
            ->where('term_taxonomy_id', '!=', $id)
            ->get();

        return view('admin.product-taxonomies.edit', compact('taxonomy', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $tt = TermTaxonomy::findOrFail($id);

        if (!$tt->term) {
            return redirect()
                ->route('product-taxonomies.index')
                ->with('error', 'The taxonomy term is missing or invalid.');
        }

        if ($request->input('parent') === '' || $request->input('parent') === '0') {
            $request->merge(['parent' => null]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                Rule::unique('terms', 'slug')->ignore($tt->term_id),
            ],
            'description' => 'nullable|string',
            'parent' => 'nullable|exists:term_taxonomies,term_taxonomy_id',
            'status' => 'required|boolean',
            'image_ids' => 'nullable|array',
            'image_ids.*' => 'exists:media,id',
        ]);

        // update term
        $tt->term->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
        ]);

        // update taxonomy
        $tt->update([
            'description' => $data['description'] ?? null,
            'parent' => $data['parent'] ?? 0,
            'status' => $data['status'],
        ]);

        // **ONLY delete pivot rows**, keep actual media files intact**
        $tt->images()->delete();

        // re-create associations
        if (!empty($data['image_ids'])) {
            foreach ($data['image_ids'] as $mid) {
                if ($media = Media::find($mid)) {
                    TermTaxonomyImage::create([
                        'term_taxonomy_id' => $tt->term_taxonomy_id,
                        'path' => $media->path,
                    ]);
                }
            }
        }

        return redirect()
            ->route('product-taxonomies.index')
            ->with('success', 'Product category updated.');
    }

    public function destroy($id)
    {
        $tt = TermTaxonomy::findOrFail($id);

        // **ONLY delete pivot rows**, keep media files in library
        $tt->images()->delete();

        // delete term and taxonomy
        $tt->term()->delete();
        $tt->delete();

        return redirect()
            ->route('product-taxonomies.index')
            ->with('success', 'Product category deleted.');
    }
}