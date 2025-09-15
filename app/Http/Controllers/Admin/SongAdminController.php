<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;
use App\Models\Album;
use App\Models\Genre;
use Illuminate\Support\Facades\Storage;

class SongAdminController extends Controller
{
    public function index(Request $request)
{
    $query = Song::with(['album', 'genre'])->latest();

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('artist', 'like', "%{$search}%");
        });
    }

    // ✅ filter by genre_id, not string
    if ($request->filled('genre_id')) {
        $query->where('genre_id', $request->genre_id);
    }

    if ($request->filled('album_id')) {
        $query->where('album_id', $request->album_id);
    }

    $songs = $query->paginate(15)->withQueryString(); // ✅ keep filters during pagination
    $albums = Album::all();
    $genres = \App\Models\Genre::orderBy('name')->get();

    return view('admin.songs.index', compact('songs', 'albums', 'genres'));
}


    public function create()
    {
        $albums = Album::all();
        $genres = Genre::orderBy('name')->get(); // ✅ fetch genres
        return view('admin.songs.create', compact('albums', 'genres'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'artist'      => 'required|string|max:255',
            'file'        => 'required|file|mimes:mp3,wav|max:51200',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'album_id'    => 'nullable|exists:albums,id',
            'genre_id'    => 'required|exists:genres,id', // ✅
        ]);

        $filePath = $request->file('file')->store('songs', 'public');
        $coverPath = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('covers', 'public')
            : null;

        Song::create([
            'title'       => $validated['title'],
            'artist'      => $validated['artist'],
            'file_path'   => $filePath,
            'cover_image' => $coverPath,
            'album_id'    => $validated['album_id'] ?? null,
            'genre_id'    => $validated['genre_id'], // ✅
        ]);

        return redirect()->route('admin.songs.index')
            ->with('success', 'Song uploaded successfully!');
    }

    public function edit(Song $song)
    {
        $albums = Album::all();
        $genres = Genre::orderBy('name')->get();
        return view('admin.songs.edit', compact('song', 'albums', 'genres'));
    }

    public function update(Request $request, Song $song)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'artist'      => 'nullable|string|max:255',
            'album_id'    => 'nullable|exists:albums,id',
            'genre_id'    => 'required|exists:genres,id', // ✅
            'file'        => 'nullable|file|mimes:mp3,wav|max:51200',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['title', 'artist', 'album_id', 'genre_id']); // ✅ removed 'genre'

        if ($request->hasFile('cover_image')) {
            if ($song->cover_image) {
                Storage::disk('public')->delete($song->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        if ($request->hasFile('file')) {
            if ($song->file_path) {
                Storage::disk('public')->delete($song->file_path);
            }
            $data['file_path'] = $request->file('file')->store('songs', 'public');
        }

        $song->update($data);

        return redirect()->route('admin.songs.index')
            ->with('success', 'Song updated successfully!');
    }

    public function destroy(Song $song)
    {
        if ($song->file_path) {
            Storage::disk('public')->delete($song->file_path);
        }
        if ($song->cover_image) {
            Storage::disk('public')->delete($song->cover_image);
        }

        $song->delete();
        return redirect()->route('admin.songs.index')
            ->with('success', 'Song deleted.');
    }



public function approve(Song $song)
{
    $song->status = 'published';
    $song->save();

    return redirect()->back()->with('success', "✅ '{$song->title}' approved and published.");
}

public function reject(Song $song)
{
    $song->status = 'rejected';
    $song->save();

    return redirect()->back()->with('error', "❌ '{$song->title}' rejected.");
}
}
