<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Song;

class SongApiController extends Controller
{

    public function index()
    {
        $songs = Song::all()->map(function ($song) {
        return [
            'id' => $song->id,
            'title' => $song->title,
            'artist' => $song->artist,
            'audio_url' => $song->file_path ? asset('storage/' . $song->file_path) : null,
            'cover_image_url' => $song->cover_image ? asset('storage/' . $song->cover_image) : null,
            'album' => $song->album ? [
                'id' => $song->album->id,
                'name' => $song->album->name,
                'cover_image_url' => $song->album->cover_image ? asset('storage/' . $song->album->cover_image) : null,
                ] : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $songs,
        ]);
    
    }

    /**
     * Generate full URL for song file.
     */
    private function getSongUrl($path)
    {
        if (!$path) return null;
        return url(Storage::url('songs/' . $path));
    }

    /**
     * Generate full URL for cover image.
     */
    private function getCoverUrl($path)
    {
        if (!$path) return null;
        return url(Storage::url('covers/' . $path));
    }


    public function show($id)
    {
        $song = Song::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist,
                'audio_url' => asset('storage/' . $song->file_path),
                'cover_image_url' => $song->cover_image ? asset('storage/' . $song->cover_image) : null,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
        'title' => 'required|string|max:255',
        'artist' => 'nullable|string|max:255',
        'file' => 'required|file|mimes:mp3,wav|max:51200',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'album_id' => 'nullable|exists:albums,id',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers', 'public');
        }

        $filePath = $request->file('file')->store('songs', 'public');

        $song = Song::create([
            'title' => $request->title,
            'artist' => $request->artist,
            'file_path' => $filePath,
            'cover_image' => $coverPath,
            'album_id' => $validated['album_id'] ?? null,
        ]);

        return response()->json([
        'success' => true,
        'message' => 'Song uploaded successfully',
        'data' => $song,
        ]);
    }

    public function update(Request $request, $id)
    {
        $song = Song::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'file' => 'nullable|mimes:mp3,wav,ogg|max:10000',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'album_id' => 'nullable|exists:albums,id',
        ]);

        // Replace song file if uploaded
        if ($request->hasFile('file')) {
            if ($song->file_path) {
                Storage::disk('public')->delete($song->file_path);
            }
            $song->file_path = $request->file('file')->store('songs', 'public');
        }

        // Replace cover image if uploaded
        if ($request->hasFile('cover_image')) {
            if ($song->cover_image) {
                Storage::disk('public')->delete($song->cover_image);
            }
            $song->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        // Update other fields
        $song->title = $request->title;
        $song->artist = $request->artist;
        $song->album_id = $request->album_id;
        $song->save();

        return response()->json([
            'message' => 'Song updated successfully',
            'song' => [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist,
                'audio_url' => asset('storage/' . $song->file_path),
                'cover_image_url' => $song->cover_image ? asset('storage/' . $song->cover_image) : null,
                'album_id' => $song->album_id,
            ]
        ]);
    }

    public function destroy($id)
    {
        $song = Song::findOrFail($id);

        // Delete files
        if ($song->file_path) {
            Storage::disk('public')->delete($song->file_path);
        }
        if ($song->cover_image) {
            Storage::disk('public')->delete($song->cover_image);
        }

        $song->delete();

        return response()->json(['message' => 'Song deleted successfully']);
    }
}
