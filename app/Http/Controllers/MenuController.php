<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

        $baseSlug = Str::slug($request->name);
        $slug = $baseSlug;
        $counter = 1;
        while (Menu::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $menu = Menu::create([
            'name' => $request->name,
            'slug' => $slug,
            'location' => $request->location,
            'auto_add_pages' => $request->has('auto_add_pages'),
        ]);

        return redirect()->route('menus.edit', $menu)
            ->with('success', 'Menu created!');
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

        $baseSlug = Str::slug($request->name);
        $slug = $baseSlug;
        $counter = 1;
        while (
            Menu::where('slug', $slug)
                ->where('id', '!=', $menu->id)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
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
        return redirect()->route('menus.index')
            ->with('success', 'Menu deleted.');
    }

    public function updateStructure(Request $request, Menu $menu)
    {
        // Validate that 'items' is an array; skip individual item fields
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($menu, $request) {
                MenuItem::where('menu_id', $menu->id)->delete();
                foreach ($request->items as $index => $item) {
                    $this->createMenuItem(
                        $menu->id,
                        (array) $item,
                        null,
                        $index
                    );
                }
            });

            return response()->json(['success' => true], 200);
        } catch (\Throwable $e) {
            Log::error("Menu structure update failed for menu {$menu->id}: {$e->getMessage()}");
            return response()->json([
                'error' => 'Unable to save menu structure.',
            ], 500);
        }
    }

    /**
     * Recursively create menu items (and children) with correct URLs.
     */
    private function createMenuItem(int $menuId, array $item, int $parentId = null, int $order = 0)
    {
        $resolvedUrl = $item['url'] ?? '#';

        if (($item['type'] ?? '') === 'page' && !empty($item['reference_id'])) {
            $page = Post::where('id', $item['reference_id'])
                ->where('type', 'page')
                ->first();
            if ($page) {
                $resolvedUrl = route('page.show', ['slug' => $page->slug]);
            }
        } elseif (($item['type'] ?? '') === 'category' && !empty($item['reference_id'])) {
            $cat = Category::find($item['reference_id']);
            if ($cat) {
                $resolvedUrl = route('category.show', $cat->slug);
            }
        }

        $menuItem = MenuItem::create([
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'title' => $item['title'] ?? '',
            'type' => $item['type'] ?? 'custom',
            'reference_id' => $item['reference_id'] ?? null,
            'url' => $resolvedUrl,
            'order' => $order,
        ]);

        if (!empty($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as $i => $child) {
                $this->createMenuItem(
                    $menuId,
                    (array) $child,
                    $menuItem->id,
                    $i
                );
            }
        }
    }

    public function addPageToMenu(Request $request, Menu $menu)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        $post = Post::where('id', $request->post_id)
            ->where('type', 'page')
            ->firstOrFail();

        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => $post->title,
            'type' => 'page',
            'reference_id' => $post->id,
            'order' => $menu->items()->count(),
        ]);

        return redirect()->route('menus.edit', $menu)
            ->with('success', 'Page added to menu.');
    }
}