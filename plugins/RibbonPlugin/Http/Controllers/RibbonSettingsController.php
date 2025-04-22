<?php

namespace Plugins\RibbonPlugin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Plugins\RibbonPlugin\Models\HeaderRibbon;

class RibbonSettingsController extends Controller
{
    public function edit()
    {
        $ribbon = HeaderRibbon::first();
        return view('ribbon-plugin::edit', compact('ribbon'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'left_text' => 'nullable|string|max:255',
            'center_text' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'bg_color' => 'required|string',
            'text_color' => 'required|string',
            'height' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        $ribbon = HeaderRibbon::first();

        if ($ribbon) {
            $ribbon->update($data);
        } else {
            HeaderRibbon::create($data);
        }

        return redirect()
            ->route('ribbon-plugin.settings.edit')
            ->with('success', 'Ribbon settings saved.');
    }
}