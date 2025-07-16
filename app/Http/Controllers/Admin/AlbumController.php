<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = Album::with('songs')->get();
        return view('admin.albums.index', compact('albums'));
    }

    public function create()
    {
        $albums = Album::all();
        return view('admin.albums.create', compact('albums'));
    }

    public function edit(Song $song)
    {
        $albums = Album::all();
        return view('admin.albums.edit', compact('albums'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $coverImage = null;
        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image')->store('covers', 'public');
        }

        $album = Album::create([
            'name' => $request->name,
            'cover_image' => $coverImage,
        ]);

        return redirect()->route('admin.albums.index')->with('success', 'Album created successfully!');
    }

    public function update(Request $request, Album $album)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($album->cover_image) {
                Storage::disk('public')->delete($album->cover_image);
            }
            $album->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        $album->name = $request->name;
        $album->save();

        return redirect()->route('admin.albums.index')->with('success', 'Album updated!');
    }

    public function destroy(Album $album)
    {
        if ($album->cover_image) {
            Storage::disk('public')->delete($album->cover_image);
        }
        $album->songs()->update(['album_id' => null]); // detach songs
        $album->delete();

        return redirect()->route('admin.albums.index')->with('success', 'Album deleted!');
    }
}

