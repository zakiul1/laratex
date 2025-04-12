<?php
namespace App\Http\Controllers;

use App\Models\Ribbon;
use Illuminate\Http\Request;

class RibbonController extends Controller
{
    public function index()
    {
        $ribbons = Ribbon::all();
        return view('ribbons.index', compact('ribbons'));
    }

    public function create()
    {
        return view('ribbons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'left_text' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'bg_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
        ]);

        Ribbon::create($request->all());

        return redirect()->route('ribbons.index')->with('success', 'Ribbon created successfully!');
    }

    public function edit(Ribbon $ribbon)
    {
        return view('ribbons.edit', compact('ribbon'));
    }

    public function update(Request $request, Ribbon $ribbon)
    {
        $request->validate([
            'left_text' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'bg_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
        ]);

        $ribbon->update($request->all());

        return redirect()->route('ribbons.index')->with('success', 'Ribbon updated successfully!');
    }

    public function destroy(Ribbon $ribbon)
    {
        $ribbon->delete();

        return redirect()->route('ribbons.index')->with('success', 'Ribbon deleted successfully!');
    }
}