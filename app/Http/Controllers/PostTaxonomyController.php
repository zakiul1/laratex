<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\TermTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostTaxonomyController extends Controller
{
    public function index(Request $request)
    {
        $taxonomies = TermTaxonomy::select('term_taxonomies.*')
            ->join('terms', 'term_taxonomies.term_id', '=', 'terms.id')
            ->where('taxonomy', 'category')
            ->with('term', 'parentTaxonomy.term', 'images')
            ->orderBy('terms.name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.post-taxonomies.index', compact('taxonomies'));
    }

    public function create()
    {
        $parents = TermTaxonomy::select('term_taxonomies.*')
            ->join('terms', 'term_taxonomies.term_id', '=', 'terms.id')
            ->where('taxonomy', 'category')
            ->with('term')
            ->orderBy('terms.name', 'asc')
            ->get();

        // no images yet
        $initialImages = [];

        return view('admin.post-taxonomies.create', compact('parents', 'initialImages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:terms,slug',
            'parent' => 'nullable|integer|exists:term_taxonomies,term_taxonomy_id',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'image_ids' => 'nullable|array',
            'image_ids.*' => 'integer|exists:media,id',
        ]);

        // create term
        $slug = $data['slug'] ?? Str::slug($data['name']);
        $term = Term::create(['name' => $data['name'], 'slug' => $slug]);

        // create taxonomy
        $taxonomy = TermTaxonomy::create([
            'term_id' => $term->id,
            'taxonomy' => 'category',
            'parent' => $data['parent'] ?? 0,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'count' => 0,
        ]);

        // attach images
        if (!empty($data['image_ids'])) {
            foreach ($data['image_ids'] as $mid) {
                $taxonomy->images()->create(['media_id' => $mid]);
            }
        }

        return redirect()
            ->route('post-taxonomies.index')
            ->with('success', 'Category created.');
    }

    public function edit($id)
    {
        $taxonomy = TermTaxonomy::with('term', 'images', 'parentTaxonomy.term')
            ->where('term_taxonomy_id', $id)
            ->where('taxonomy', 'category')
            ->firstOrFail();

        $parents = TermTaxonomy::select('term_taxonomies.*')
            ->join('terms', 'term_taxonomies.term_id', '=', 'terms.id')
            ->where('taxonomy', 'category')
            ->where('term_taxonomy_id', '!=', $id)
            ->with('term')
            ->orderBy('terms.name', 'asc')
            ->get();

        // prepare Alpine initialImages
        $initialImages = $taxonomy->images->map(fn($img) => [
            'id' => $img->media_id,
            'thumbnail' => Storage::url($img->path),
        ])->toArray();

        return view('admin.post-taxonomies.edit', compact('taxonomy', 'parents', 'initialImages'));
    }

    public function update(Request $request, $id)
    {
        $taxonomy = TermTaxonomy::with('term', 'images')
            ->where('term_taxonomy_id', $id)
            ->where('taxonomy', 'category')
            ->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', Rule::unique('terms', 'slug')->ignore($taxonomy->term_id, 'id')],
            'parent' => ['nullable', 'integer', Rule::exists('term_taxonomies', 'term_taxonomy_id')->where('taxonomy', 'category')],
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'image_ids' => 'nullable|array',
            'image_ids.*' => 'integer|exists:media,id',
        ]);

        // update term
        $taxonomy->term->update([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
        ]);

        // update taxonomy fields
        $taxonomy->update([
            'parent' => $data['parent'] ?? 0,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
        ]);

        // sync images
        $taxonomy->images()->delete();
        foreach ($data['image_ids'] ?? [] as $mid) {
            $taxonomy->images()->create(['media_id' => $mid]);
        }

        return redirect()
            ->route('post-taxonomies.index')
            ->with('success', 'Category updated.');
    }

    public function destroy($id)
    {
        $taxonomy = TermTaxonomy::with('term')
            ->where('term_taxonomy_id', $id)
            ->where('taxonomy', 'category')
            ->firstOrFail();

        $taxonomy->images()->delete();
        $taxonomy->delete();
        Term::where('id', $taxonomy->term_id)
            ->whereDoesntHave('taxonomies')
            ->delete();

        return redirect()
            ->route('post-taxonomies.index')
            ->with('success', 'Category deleted.');
    }
}