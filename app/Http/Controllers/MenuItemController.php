<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
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

        $menu = Menu::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
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

        $menu->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
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
        MenuItem::where('menu_id', $menu->id)->delete();

        foreach ($request->items as $index => $item) {
            $this->createMenuItem($menu->id, $item, null, $index);
        }

        return response()->json(['success' => true]);
    }

    private function createMenuItem($menuId, $item, $parentId = null, $order = 0)
    {
        $menuItem = MenuItem::create([
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'title' => $item['title'],
            'url' => $item['url'] ?? null,
            'type' => $item['type'] ?? 'custom',
            'reference_id' => $item['reference_id'] ?? null,
            'order' => $order,
        ]);

        if (!empty($item['children'])) {
            foreach ($item['children'] as $i => $child) {
                $this->createMenuItem($menuId, $child, $menuItem->id, $i);
            }
        }
    }
}
