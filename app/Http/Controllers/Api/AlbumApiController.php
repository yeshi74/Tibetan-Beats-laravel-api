<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Album;
use Illuminate\Support\Facades\Storage;

class AlbumApiController extends Controller
{

    public function index()
    {
        $albums = Album::with('songs')->get();

        $albumsData = $albums->map(function ($album) {
            return [
                'id' => $album->id,
                'name' => $album->name,
                'cover_image_url' => $album->cover_image ? asset('storage/' . $album->cover_image) : null,
                'songs' => $album->songs->map(function ($song) {
                    return [
                        'id' => $song->id,
                        'title' => $song->title,
                        'artist' => $song->artist,
                        'audio_url' => $song->file_path ? asset('storage/' . $song->file_path) : null,
                        'cover_image_url' => $song->cover_image ? asset('storage/' . $song->cover_image) : null,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $albumsData,
        ]);
    }

    public function show($id)
    {
        $album = Album::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $album->id,
                'name' => $album->title,
                'cover_image_url' => $album->cover_image ? asset('storage/' . $album->cover_image) : null,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $coverImagePath = null;

        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('covers', 'public');
        }

        $album = Album::create([
            'name' => $request->name,
            'cover_image' => $coverImagePath,
        ]);

        return response()->json([
            'message' => 'Album created successfully',
            'album' => [
                'id' => $album->id,
                'name' => $album->name,
                'cover_image_url' => $album->cover_image ? asset('storage/' . $album->cover_image) : null,
            ]
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $album = Album::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // If new image is uploaded, delete old one
        if ($request->hasFile('cover_image')) {
            if ($album->cover_image) {
                Storage::disk('public')->delete($album->cover_image);
            }
            $album->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        $album->name = $request->name;
        $album->save();

        return response()->json([
            'message' => 'Album updated successfully',
            'album' => [
                'id' => $album->id,
                'name' => $album->name,
                'cover_image_url' => $album->cover_image ? asset('storage/' . $album->cover_image) : null,
            ]
        ]);
    }

    public function destroy($id)
    {
        $album = Album::findOrFail($id);

        // Delete album cover image if exists
        if ($album->cover_image) {
            Storage::disk('public')->delete($album->cover_image);
        }

        $album->delete();

        return response()->json(['message' => 'Album deleted successfully']);
    }

}




