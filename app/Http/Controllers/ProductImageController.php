<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\JsonResponse;

class ProductImageController extends Controller
{
    public function destroy($id): JsonResponse
    {
        $image = ProductImage::findOrFail($id);

        // Delete the image from storage
        if ($image->image && File::exists(public_path('storage/' . $image->image))) {
            File::delete(public_path('storage/' . $image->image));
        }

        // Delete the record
        $image->delete();

        return response()->json(['success' => true]);
    }
}