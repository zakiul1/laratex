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

        $slug = Str::slug($request->name);
        $counter = 1;
        while (Menu::where('slug', $slug)->exists()) {
            $slug = Str::slug($request->name) . '-' . $counter++;
        }

        $menu = Menu::create([
            'name' => $request->name,
            'slug' => $slug,
            'location' => $request->location,
            'auto_add_pages' => $request->has('auto_add_pages'),
        ]);

        return redirect()
            ->route('menus.edit', $menu)
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

        $slug = Str::slug($request->name);
        $counter = 1;
        while (Menu::where('slug', $slug)->where('id', '!=', $menu->id)->exists()) {
            $slug = Str::slug($request->name) . '-' . $counter++;
        }

        $menu->update([
            'name' => $request->name,
            'slug' => $slug,
            'location' => $request->location,
            'auto_add_pages' => $request->has('auto_add_pages'),
        ]);

        return back()->with('success', 'Menu updated!');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()
            ->route('menus.index')
            ->with('success', 'Menu deleted.');
    }

    /**
     * Save the drag-and-drop structure.
     */
    public function updateStructure(Request $request, Menu $menu)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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
            return response()->json(['error' => 'Unable to save menu structure.'], 500);
        }
    }

    /**
     * Recursively create menu items (and children) with correct URLs.
     */
    private function createMenuItem(int $menuId, array $item, int $parentId = null, int $order = 0)
    {
        $url = $item['url'] ?? '#';

        if (($item['type'] ?? '') === 'page' && !empty($item['reference_id'])) {
            $page = Post::where('id', $item['reference_id'])
                ->where('type', 'page')
                ->first();
            if ($page) {
                $url = route('page.show', $page->slug);
            }
        } elseif (($item['type'] ?? '') === 'category' && !empty($item['reference_id'])) {
            $cat = Category::find($item['reference_id']);
            if ($cat) {
                $url = route('categories.show', $cat->slug);
            }
        }

        $menuItem = MenuItem::create([
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'title' => $item['title'] ?? '',
            'type' => $item['type'] ?? 'custom',
            'reference_id' => $item['reference_id'] ?? null,
            'url' => $url,
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

    /**
     * Add a Page to the menu.
     */
    public function addPageToMenu(Request $request, Menu $menu)
    {
        $data = $request->validate(['post_id' => 'required|exists:posts,id']);

        try {
            $post = Post::findOrFail($data['post_id']);

            $menu->items()->create([
                'title' => $post->title,
                'type' => 'page',
                'reference_id' => $post->id,
                'url' => route('page.show', $post->slug),
                'order' => $menu->items()->count(),
            ]);

            return back()->with('success', 'Page added to menu.');
        } catch (\Throwable $e) {
            Log::error("Failed to add page to menu {$menu->id}: " . $e->getMessage());
            return back()->with('error', 'Could not add page.');
        }
    }

    /**
     * Add a Category to the menu.
     */
    public function addCategoryToMenu(Request $request, Menu $menu)
    {
        $data = $request->validate(['category_id' => 'required|exists:categories,id']);

        try {
            $cat = Category::findOrFail($data['category_id']);

            $menu->items()->create([
                'title' => $cat->name,
                'type' => 'category',
                'reference_id' => $cat->id,
                'url' => route('categories.show', $cat->slug),
                'order' => $menu->items()->count(),
            ]);

            return back()->with('success', 'Category added to menu.');
        } catch (\Throwable $e) {
            Log::error("Failed to add category to menu {$menu->id}: " . $e->getMessage());
            return back()->with('error', 'Could not add category.');
        }
    }

    /**
     * Add a generic Post to the menu.
     */
    public function addPostToMenu(Request $request, Menu $menu)
    {
        $data = $request->validate(['post_id' => 'required|exists:posts,id']);

        try {
            $post = Post::findOrFail($data['post_id']);

            $menu->items()->create([
                'title' => $post->title,
                'type' => 'post',
                'reference_id' => $post->id,
                'url' => route('posts.show', $post->slug),
                'order' => $menu->items()->count(),
            ]);

            return back()->with('success', 'Post added to menu.');
        } catch (\Throwable $e) {
            Log::error("Failed to add post to menu {$menu->id}: " . $e->getMessage());
            return back()->with('error', 'Could not add post.');
        }
    }
}