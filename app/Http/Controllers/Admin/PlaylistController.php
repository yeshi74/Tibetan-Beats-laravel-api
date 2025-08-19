<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::withCount('songs')->latest()->paginate(10);
        return view('admin.playlists.index', compact('playlists'));
    }

    public function create()
    {
        $songs = Song::latest()->get();
        return view('admin.playlists.create', compact('songs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'songs' => 'array',
        ]);

        $playlist = Playlist::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if (!empty($data['songs'])) {
            $playlist->songs()->sync($data['songs']);
        }

        return redirect()->route('admin.playlists.index')
            ->with('success', 'Playlist created successfully!');
    }

    public function edit(Playlist $playlist)
    {
        $songs = Song::latest()->get();
        $selectedSongs = $playlist->songs->pluck('id')->toArray();
        return view('admin.playlists.edit', compact('playlist', 'songs', 'selectedSongs'));
    }

    public function update(Request $request, Playlist $playlist)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'songs' => 'array',
        ]);

        $playlist->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $playlist->songs()->sync($data['songs'] ?? []);

        return redirect()->route('admin.playlists.index')
            ->with('success', 'Playlist updated successfully!');
    }

    public function destroy(Playlist $playlist)
    {
        $playlist->delete();
        return redirect()->route('admin.playlists.index')
            ->with('success', 'Playlist deleted successfully!');
    }
}
