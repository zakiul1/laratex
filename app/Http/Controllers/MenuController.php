<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('children')->whereNull('parent_id')->orderBy('order')->get();
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        $menus = Menu::all();
        return view('menus.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menus,slug',
            'position' => 'required|in:center,right',
            'parent_id' => 'nullable|exists:menus,id',
        ]);

        Menu::create($request->all());

        return redirect()->route('menus.index')->with('success', 'Menu created successfully.');
    }

    public function edit(Menu $menu)
    {
        $menus = Menu::where('id', '!=', $menu->id)->get(); // Exclude self
        return view('menus.edit', compact('menu', 'menus'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menus,slug,' . $menu->id,
            'position' => 'required|in:center,right',
            'parent_id' => 'nullable|exists:menus,id',
        ]);

        $menu->update($request->all());

        return redirect()->route('menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Menu deleted successfully.');
    }

    public function order(Request $request)
    {
        $data = $request->input('order');
        foreach ($data as $index => $item) {
            Menu::where('id', $item['id'])->update([
                'order' => $index,
                'parent_id' => $item['parent_id'],
            ]);
        }

        return response()->json(['success' => true]);
    }
}