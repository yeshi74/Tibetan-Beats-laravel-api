<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;
use App\Models\Album;

class SongAdminController extends Controller
{
    public function index(Request $request)
    {
        $songs = Song::latest()->get();

        $query = Song::query();

        if ($request->has('search')) {
        $search = $request->search;
        $query->where('title', 'like', "%{$search}%")
              ->orWhere('artist', 'like', "%{$search}%");
        }

        $songs = $query->latest()->paginate(10);

        return view('admin.songs.index', compact('songs'));
    }

    public function create()
    {
        $albums = Album::all();
        return view('admin.songs.create', compact('albums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
        'title' => 'required|string|max:255',
        'artist' => 'required|string|max:255',
        'file' => 'required|file|mimes:mp3,wav',
        'cover_image' => 'nullable|image|mimes:jpg,jpeg,png',
        'album_id' => 'nullable|exists:albums,id',
    ]);


    // Store the audio file
    $filePath = $request->file('file')->store('songs', 'public');

    // Store cover image (optional)
    $coverPath = $request->hasFile('cover_image')
        ? $request->file('cover_image')->store('covers', 'public')
        : null;


    // Save to DB
    Song::create([
        'title' => $validated['title'],
        'artist' => $validated['artist'],
        'file_path' => $filePath,
        'cover_image' => $coverPath,
        'album_id' => $validated['album_id'] ?? null,
    ]);

    return redirect()->route('admin.songs.index')->with('success', 'Song uploaded successfully!');
    
    }

    public function edit(Song $song)
    {
        $albums = Album::all();
        return view('admin.songs.edit', compact('song', 'albums'));
    }

    public function update(Request $request, Song $song)
    {
        $request->validate([
            'title' => 'required|string',
            'artist' => 'nullable|string',
            'album_id' => 'nullable|exists:albums,id',
            'file' => 'nullable|file|mimes:mp3,wav|max:51200',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

        ]);

        $data = $request->only(['title', 'artist', 'cover_image']);

        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers', 'public');
            $data['cover_image'] = $coverPath;
        }

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('songs', 'public');
            $data['file_path'] = $filePath;
        }

        $song->album_id = $request->album_id;

        $song->update($data);

        return redirect()->route('admin.songs.index')->with('success', 'Song updated successfully!');
    }

    public function destroy(Song $song)
    {
        $song->delete();
        return redirect()->route('admin.songs.index')->with('success', 'Song deleted.');
    }

}

