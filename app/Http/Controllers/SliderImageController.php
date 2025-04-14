<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\SliderImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderImageController extends Controller
{
    public function upload(Request $request, Slider $slider)
    {
        $request->validate([
            'images.*' => 'required|image|max:5120'
        ]);

        foreach ($request->file('images') as $img) {
            $path = $img->store('sliders', 'public');
            $slider->images()->create(['path' => $path]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $image = SliderImage::find($id);

        if (!$image) {
            return response()->json(['success' => false, 'message' => 'Image not found'], 404);
        }

        Storage::disk('public')->delete($image->image);
        $image->delete();

        return response()->json(['success' => true]);
    }


}