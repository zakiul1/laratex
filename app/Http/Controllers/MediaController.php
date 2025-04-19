<?php
namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\MediaFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $q = Media::query();

        // — simple search/filter
        if ($request->filled('search')) {
            $q->where('original_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('folder_id')) {
            $q->where('folder_id', $request->folder_id);
        }

        $media = $q->latest()->paginate(24);
        $folders = MediaFolder::with('children')->get();

        return view('media.index', compact('media', 'folders'));
    }

    public function create()
    {
        $folders = MediaFolder::all();
        return view('media.create', compact('folders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|mimes:jpeg,png,gif,svg,webp|max:10240'
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('media');

            // optionally read EXIF
            try {
                $exif = @exif_read_data($file->getRealPath());
            } catch (\Throwable $e) {
                $exif = null;
            }

            $media = Media::create([
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'folder_id' => $request->folder_id,
            ]);

            // store a few EXIF fields
            if ($exif) {
                foreach (['Model', 'Make', 'DateTimeOriginal'] as $key) {
                    if (!empty($exif[$key])) {
                        $media->metas()->create([
                            'meta_key' => 'exif_' . $key,
                            'meta_value' => $exif[$key],
                        ]);
                    }
                }
            }
        }

        return redirect()->route('media.index')->with('success', 'Uploaded!');
    }

    public function edit(Media $media)
    {
        $folders = MediaFolder::all();
        return view('media.edit', compact('media', 'folders'));
    }

    public function update(Request $request, Media $media)
    {
        $data = $request->validate([
            'original_name' => 'string|required',
            'alt_text' => 'nullable|string',
            'caption' => 'nullable|string',
            'folder_id' => 'nullable|exists:media_folders,id',
        ]);

        $media->update($data);

        // cropping/resizing example:
        if ($request->filled('crop_data')) {
            $img = Image::make(Storage::path($media->file_name))
                ->crop(
                    $request->crop_data['width'],
                    $request->crop_data['height'],
                    $request->crop_data['x'],
                    $request->crop_data['y']
                )
                ->save();
        }

        return back()->with('success', 'Saved!');
    }

    public function destroy(Media $media)
    {
        Storage::delete($media->file_name);
        $media->delete();
        return back()->with('success', 'Deleted!');
    }
}