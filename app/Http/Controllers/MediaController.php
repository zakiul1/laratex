<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index()
    {
        // Return Blade view with all media
        $items = Media::latest()->get();
        return view('media.index', compact('items'));
    }

    public function store(Request $request)
    {
        // Validate multiple files
        $request->validate([
            'files.*' => 'required|file|mimes:jpg,jpeg,png,gif,svg|max:5120', // max 5MB
        ]);

        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $path = $file->store('media', 'public');
            $media = Media::create([
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
            $uploaded[] = [
                'id' => $media->id,
                'url' => Storage::url($path),
                'filename' => $media->filename,
            ];
        }

        return response()->json(['uploaded' => $uploaded], 201);
    }

    public function destroy(Media $media)
    {
        Storage::disk('public')->delete($media->path);
        $media->delete();
        return response()->json(['deleted' => true], 200);
    }
}