<?php

namespace App\Http\Controllers;

use App\Models\Widget;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    public function index()
    {
        $widgets = Widget::orderBy('widget_area')
            ->orderBy('order')
            ->get()
            ->groupBy('widget_area');

        return view('widgets.index', compact('widgets'));
    }

    public function create()
    {
        return view('widgets.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        // Centralized validation
        $data = $this->validateWidget($request);

        // Normalize the "Active" checkbox
        $data['status'] = $request->has('status') ? 1 : 0;

        Widget::create($data);

        return redirect()
            ->route('widgets.index')
            ->with('success', 'Widget created.');
    }

    public function edit(Widget $widget)
    {
        return view('widgets.edit', compact('widget'));
    }

    public function update(Request $request, Widget $widget)
    {
        $data = $this->validateWidget($request);
        $data['status'] = $request->has('status') ? 1 : 0;

        $widget->update($data);

        return redirect()
            ->route('widgets.index')
            ->with('success', 'Widget updated.');
    }

    public function destroy(Widget $widget)
    {
        $widget->delete();

        return redirect()
            ->route('widgets.index')
            ->with('success', 'Widget deleted.');
    }

    /**
     * Validate everything—including dynamic 'content' rules.
     */
    protected function validateWidget(Request $request): array
    {
        // Base rules for all widgets
        $rules = [
            'title' => 'nullable|string|max:255',
            'widget_type' => 'required|in:text,view,menu,category',
            'widget_area' => 'required|string',
            'order' => 'required|integer|min:0',
        ];

        // Apply the right rule to content based on the selected type:
        switch ($request->input('widget_type')) {
            case 'text':
                $rules['content'] = 'required|string';
                break;

            case 'view':
                $rules['content'] = 'required|string';
                break;

            case 'menu':
                // content must be a menu ID
                $rules['content'] = 'required|integer|exists:menus,id';
                break;

            case 'category':
                // content must be a category slug
                $rules['content'] = 'required|string|exists:categories,slug';
                break;
        }

        return $request->validate($rules);
    }
}