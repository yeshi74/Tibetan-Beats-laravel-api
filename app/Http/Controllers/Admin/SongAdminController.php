<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;
use App\Models\Album;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SongAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Song::query()->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist', 'like', "%{$search}%")
                  ->orWhere('genre', 'like', "%{$search}%");
            });
        }

        if ($request->filled('genre')) {
            $query->where('genre', $request->genre);
        }

        if ($request->filled('album_id')) {
            $query->where('album_id', $request->album_id);
        }

        $songs = $query->paginate(15)->withQueryString();
        $albums = Album::all();

        return view('admin.songs.index', compact('songs', 'albums'));
    }

    public function create()
    {
        $albums = Album::all();
        return view('admin.songs.create', compact('albums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'artist'      => 'nullable|string|max:255',
            'genre'       => 'nullable|string|max:100',
            'file'        => 'required|file|mimes:mp3,wav,aac,flac|max:102400', // 100MB
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // 5MB
            'album_id'    => 'nullable|exists:albums,id',
        ]);

        $filePath = $request->file('file')->store('songs', 'public');
        $coverPath = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('covers', 'public')
            : null;

        Song::create([
            'title'       => $validated['title'],
            'artist'      => $validated['artist'] ?? 'Unknown Artist',
            'genre'       => $validated['genre'] ?? null,
            'file_path'   => $filePath,
            'cover_image' => $coverPath,
            'album_id'    => $validated['album_id'] ?? null,
            'slug'        => Str::slug($validated['title']) . '-' . Str::random(6),
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
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'artist'      => 'nullable|string|max:255',
            'genre'       => 'nullable|string|max:100',
            'file'        => 'nullable|file|mimes:mp3,wav,aac,flac|max:102400',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'album_id'    => 'nullable|exists:albums,id',
        ]);

        $data = $request->only(['title', 'artist', 'genre', 'album_id']);

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

        return redirect()->route('admin.songs.index')->with('success', 'Song updated successfully!');
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
        return redirect()->route('admin.songs.index')->with('success', 'Song deleted.');
    }
}
