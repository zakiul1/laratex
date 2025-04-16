<?php
namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function store(Request $request, $menuId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:custom,page,post,category',
            'url' => 'nullable|url',
            'reference_id' => 'nullable|numeric',
            'parent_id' => 'nullable|exists:menu_items,id',
            'order' => 'nullable|integer',
        ]);

        $menu = Menu::findOrFail($menuId);

        $menuItem = new MenuItem([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'url' => $validated['type'] === 'custom' ? $validated['url'] : null,
            'reference_id' => $validated['reference_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'order' => $validated['order'] ?? 0,
        ]);

        $menu->items()->save($menuItem);

        return back()->with('success', 'Menu item added.');
    }

    public function edit(MenuItem $menuItem)
    {
        $pages = Page::pluck('title', 'id');
        return view('menu_items.edit', compact('menuItem', 'pages'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:custom,page,post,category',
            'url' => 'nullable|url',
            'reference_id' => 'nullable|numeric',
            'parent_id' => 'nullable|exists:menu_items,id',
            'order' => 'nullable|integer',
        ]);

        $menuItem->update([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'url' => $validated['type'] === 'custom' ? $validated['url'] : null,
            'reference_id' => $validated['reference_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'order' => $validated['order'] ?? 0,
        ]);

        return back()->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();
        return back()->with('success', 'Menu item deleted.');
    }
}