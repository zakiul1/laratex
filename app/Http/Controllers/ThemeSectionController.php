<?php

namespace App\Http\Controllers;

use App\Models\ThemeSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ThemeSectionController extends Controller
{
    public function index()
    {
        $theme = getActiveTheme();
        $sections = ThemeSection::where('theme', $theme)->orderBy('order')->get();
        return view('admin.theme_sections.index', compact('sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'title' => 'nullable|string',
        ]);

        $theme = getActiveTheme();

        ThemeSection::create([
            'theme' => $theme,
            'key' => Str::slug($request->key),
            'title' => $request->title ?? $request->key,
            'order' => ThemeSection::where('theme', $theme)->max('order') + 1,
        ]);

        return back()->with('success', 'Section added.');
    }

    public function updateOrder(Request $request)
    {
        foreach ($request->order as $index => $id) {
            ThemeSection::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(ThemeSection $section)
    {
        $section->delete();
        return back()->with('success', 'Section removed.');
    }
}
