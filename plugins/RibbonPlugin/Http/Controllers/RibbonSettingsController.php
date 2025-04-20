<?php
namespace Plugins\RibbonPlugin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Plugins\RibbonPlugin\Models\RibbonSetting;

class RibbonSettingsController extends Controller
{
    public function index()
    {
        $setting = RibbonSetting::first();
        return view('ribbon-plugin::settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'left_text' => 'required|string|max:255',
            'rfq_text'  => 'required|string|max:255',
            'rfq_url'   => 'nullable|url|max:255',
            'phone'     => 'required|string|max:50',
            'email'     => 'required|email|max:100',
        ]);

        RibbonSetting::updateOrCreate([], $data);

        return redirect()
               ->route('ribbon-plugin.settings.index')
               ->with('success','Ribbon settings saved.');
    }
}
