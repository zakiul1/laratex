<?php
namespace App\Http\Controllers;

use App\Models\Widget;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    public function index()
    {
        $widgets = Widget::orderBy('widget_area')->orderBy('order')->get()->groupBy('widget_area');
        return view('widgets.index', compact('widgets'));
    }

    public function create()
    {
        return view('widgets.create');
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'widget_type' => 'required|string',
            'widget_area' => 'required|string',
            'order' => 'nullable|integer',
            'status' => 'boolean'
        ]);

        Widget::create($data);
        return redirect()->route('widgets.index')->with('success', 'Widget created.');
    }

    public function edit(Widget $widget)
    {
        return view('widgets.edit', compact('widget'));
    }

    public function update(Request $request, Widget $widget)
    {

        $data = $request->validate([
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'widget_type' => 'required|string',
            'widget_area' => 'required|string',
            'order' => 'nullable|integer',
            'status' => 'boolean'
        ]);

        $widget->update($data);
        return redirect()->route('widgets.index')->with('success', 'Widget updated.');
    }

    public function destroy(Widget $widget)
    {
        $widget->delete();
        return redirect()->route('widgets.index')->with('success', 'Widget deleted.');
    }
    public function reorder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array'
        ]);

        foreach ($request->orders as $id => $order) {
            Widget::where('id', $id)->update(['order' => $order]);
        }

        return response()->json(['success' => true]);
    }

}