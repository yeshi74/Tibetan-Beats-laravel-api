<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function index(Request $request)
    {
        $query = Playlist::withCount('songs')->latest();

        // 🔎 Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // ✅ Paginate + preserve search query
        $playlists = $query->paginate(10)->withQueryString();

        return view('admin.playlists.index', compact('playlists'));
    }

    public function create()
    {
        $songs = Song::latest()->get();
        return view('admin.playlists.create', compact('songs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'songs'       => 'array',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'is_featured' => 'nullable|boolean',
            'type'        => 'nullable|string|max:50',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('playlist_covers', 'public');
        }

        $playlist = Playlist::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'cover_image' => $coverPath,
            'is_featured' => $validated['is_featured'] ?? false,
            'type'        => $validated['type'] ?? null,
            'user_id'     => null, // admin-created
        ]);

        if (!empty($validated['songs'])) {
            $playlist->songs()->sync($validated['songs']);
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
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'songs'       => 'array',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'is_featured' => 'nullable|boolean',
            'type'        => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('playlist_covers', 'public');
            $validated['cover_image'] = $coverPath;
        }

        // Checkbox for "featured"
        $validated['is_featured'] = $request->has('is_featured');

        $playlist->update($validated);

        $playlist->songs()->sync($validated['songs'] ?? []);

        return redirect()->route('admin.playlists.index')
            ->with('success', 'Playlist updated successfully!');
    }

    public function destroy(Playlist $playlist)
    {
        $playlist->delete();
        return redirect()->route('admin.playlists.index')
            ->with('success', 'Playlist deleted successfully!');
    }

    public function toggleFeatured(Playlist $playlist)
    {
        $playlist->is_featured = !$playlist->is_featured;
        $playlist->save();

        return redirect()->route('admin.playlists.index')
            ->with('success', 'Playlist "' . $playlist->name . '" featured status updated!');
    }

}
