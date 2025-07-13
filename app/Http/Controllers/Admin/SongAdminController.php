<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;

class SongAdminController extends Controller
{
    public function index()
    {
        $songs = Song::latest()->get();
        return view('admin.songs.index', compact('songs'));
    }

    public function create()
    {
        return view('admin.songs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
        'title' => 'required|string',
        'artist' => 'nullable|string',
        'file' => 'required|mimes:mp3,wav|max:51200',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $data = [
        'title' => $request->title,
        'artist' => $request->artist,
    ];

    if ($request->hasFile('cover_image')) {
        $coverPath = $request->file('cover_image')->store('covers', 'public');
        $data['cover_image'] = $coverPath;
    }

    if (!$request->hasFile('file')) {
        return back()->withErrors(['file' => 'No file uploaded.']);
    }

    $file = $request->file('file');

    if (!$file->isValid()) {
        return back()->withErrors([
            'file' => 'File upload failed. Error code: ' . $file->getError(),
        ]);
    }

    $data['file_path'] = $file->store('songs', 'public');

    $song = Song::create($data);

    return redirect()->route('admin.songs.index')->with('success', '🎵 Song uploaded!');
    
    }

    public function edit(Song $song)
    {
        return view('admin.songs.edit', compact('song'));
    }

    public function update(Request $request, Song $song)
    {
        $request->validate([
            'title' => 'required|string',
            'artist' => 'nullable|string',
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

        $song->update($data);

        return redirect()->route('admin.songs.index')->with('success', 'Song updated successfully!');
    }

    public function destroy(Song $song)
    {
        $song->delete();
        return redirect()->route('admin.songs.index')->with('success', 'Song deleted.');
    }

}

