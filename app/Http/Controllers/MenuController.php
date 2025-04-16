<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::all();
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        return view('menus.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:menus,name',
            'location' => 'nullable|in:header,footer',
            'auto_add_pages' => 'nullable|boolean',
        ]);

        // ✅ Ensure unique slug for menu
        $baseSlug = Str::slug($request->name);
        $slug = $baseSlug;
        $counter = 1;

        while (Menu::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $menu = Menu::create([
            'name' => $request->name,
            'slug' => $slug,
            'location' => $request->location,
            'auto_add_pages' => $request->has('auto_add_pages'),
        ]);

        return redirect()->route('menus.edit', $menu)->with('success', 'Menu created!');
    }

    public function edit(Menu $menu)
    {
        $menu->load('items.children');
        return view('menus.edit', compact('menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:menus,name,' . $menu->id,
            'location' => 'nullable|in:header,footer',
            'auto_add_pages' => 'nullable|boolean',
        ]);

        // ✅ Also update slug if name is changed
        $baseSlug = Str::slug($request->name);
        $slug = $baseSlug;
        $counter = 1;

        while (Menu::where('slug', $slug)->where('id', '!=', $menu->id)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $menu->update([
            'name' => $request->name,
            'slug' => $slug,
            'location' => $request->location,
            'auto_add_pages' => $request->has('auto_add_pages'),
        ]);

        return redirect()->back()->with('success', 'Menu updated!');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('menus.index')->with('success', 'Menu deleted.');
    }

    public function updateStructure(Request $request, Menu $menu)
    {
        // dd($request->all());
        MenuItem::where('menu_id', $menu->id)->delete();

        foreach ($request->items as $index => $item) {
            $this->createMenuItem($menu->id, $item, null, $index);
        }

        return response()->json(['success' => true]);
    }

    private function createMenuItem($menuId, $item, $parentId = null, $order = 0)
    {
        // Automatically resolve URL based on type and reference_id
        $resolvedUrl = $item['url'] ?? null;

        if (empty($resolvedUrl)) {
            if ($item['type'] === 'page' && !empty($item['reference_id'])) {
                $page = \App\Models\Post::where('id', $item['reference_id'])->where('type', 'page')->first();
                if ($page) {
                    $resolvedUrl = '/' . $page->slug;
                }
            } elseif ($item['type'] === 'category' && !empty($item['reference_id'])) {
                $category = \App\Models\Category::find($item['reference_id']);
                if ($category) {
                    $resolvedUrl = route('category.show', $category->slug);
                }
            }
        }

        $menuItem = MenuItem::create([
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'title' => $item['title'],
            'type' => $item['type'] ?? 'custom',
            'reference_id' => $item['reference_id'] ?? null,
            'url' => $resolvedUrl,
            'order' => $order,
        ]);

        // Recursively add children
        if (!empty($item['children'])) {
            foreach ($item['children'] as $i => $child) {
                $this->createMenuItem($menuId, $child, $menuItem->id, $i);
            }
        }
    }


    public function addPageToMenu(Request $request, Menu $menu)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        // Get the post with type = page
        $post = Post::where('id', $request->post_id)->where('type', 'page')->firstOrFail();

        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => $post->title,
            'type' => 'page',
            'reference_id' => $post->id,
            'order' => $menu->items()->count(),
        ]);

        return redirect()->route('menus.edit', $menu->id)->with('success', 'Page added to menu.');
    }
}