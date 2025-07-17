<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Album;
use App\Models\Song;

class AlbumApiController extends Controller
{
    /**
     * Display a listing of albums with songs.
     */
    public function index()
    {
        $albums = Album::with('songs')->get()->map(function ($album) {
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
            'data' => $albums,
        ]);
    }

    /**
     * Store a newly created album.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers', 'public');
        }

        $album = Album::create([
            'name' => $request->name,
            'cover_image' => $coverPath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Album created successfully',
            'data' => $album,
        ]);
    }

    /**
     * Display a specific album with its songs.
     */
    public function show($id)
    {
        $album = Album::with('songs')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
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
            ],
        ]);
    }

    /**
     * Update the specified album.
     */
    public function update(Request $request, $id)
    {
        $album = Album::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($album->cover_image) {
                Storage::disk('public')->delete($album->cover_image);
            }

            $album->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        $album->name = $request->name;
        $album->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Album updated successfully',
            'data' => $album,
        ]);
    }

    /**
     * Delete the specified album.
     */
    public function destroy($id)
    {
        $album = Album::findOrFail($id);

        // Delete cover image if exists
        if ($album->cover_image) {
            Storage::disk('public')->delete($album->cover_image);
        }

        $album->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Album deleted successfully',
        ]);
    }
}
