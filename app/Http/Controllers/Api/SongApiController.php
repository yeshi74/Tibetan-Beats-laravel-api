<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Song;

class SongApiController extends Controller
{
    public function index()
    {
        $songs = Song::latest()->get()->map(function ($song) {
            return [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist,
                'audio_url' => asset('storage/' . $song->file_path),
                'cover_image_url' => $song->cover_image ? asset('storage/' . $song->cover_image) : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $songs,
        ]);
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
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Song uploaded successfully.',
            'data' => [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist,
                'audio_url' => asset('storage/' . $song->file_path),
                'cover_image_url' => $coverPath ? asset('storage/' . $coverPath) : null,
            ],
        ], 201);
    }
}
