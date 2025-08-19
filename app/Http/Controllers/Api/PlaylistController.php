<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    // List all playlists of the logged-in user (with songs count)
    public function index(Request $request)
    {
        $playlists = Playlist::where('user_id', $request->user()->id)
            ->withCount('songs')
            ->latest()
            ->get()
            ->map(function ($playlist) {
                return [
                    'id'          => $playlist->id,
                    'name'        => $playlist->name,
                    'description' => $playlist->description,
                    'songs_count' => $playlist->songs_count,
                ];
            });

        return response()->json([
            'status'    => 'success',
            'playlists' => $playlists,
        ]);
    }

    // Create a new playlist
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $playlist = Playlist::create([
            'user_id'     => $request->user()->id,
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $playlist->refresh();

        return response()->json([
            'status'   => 'success',
            'playlist' => [
                'id'          => $playlist->id,
                'name'        => $playlist->name,
                'description' => $playlist->description,
                'songs_count' => 0,
            ],
        ], 201);
    }

    // Show one playlist with its songs
    public function show(Request $request, Playlist $playlist)
    {
        $this->authorizePlaylist($request, $playlist);

        $baseUrl = $request->getSchemeAndHttpHost();

        $songs = $playlist->songs()->latest()->get()->map(function (Song $song) use ($baseUrl) {
            return [
                'id'              => $song->id,
                'title'           => $song->title,
                'artist'          => $song->artist,
                'genre'           => $song->genre,
                'audio_url'       => $baseUrl . '/api/songs/stream/' . $song->id,
                'cover_image_url' => $song->cover_image ? $baseUrl . '/storage/' . $song->cover_image : null,
            ];
        });

        return response()->json([
            'status'   => 'success',
            'playlist' => [
                'id'          => $playlist->id,
                'name'        => $playlist->name,
                'description' => $playlist->description,
                'songs_count' => $playlist->songs()->count(),
            ],
            'songs'    => $songs,
        ]);
    }

    // Rename/update playlist
    public function update(Request $request, Playlist $playlist)
    {
        $this->authorizePlaylist($request, $playlist);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $playlist->update($validated);
        $playlist->refresh();

        return response()->json([
            'status'   => 'success',
            'playlist' => [
                'id'          => $playlist->id,
                'name'        => $playlist->name,
                'description' => $playlist->description,
                'songs_count' => $playlist->songs()->count(),
            ],
        ]);
    }

    // Delete playlist
    public function destroy(Request $request, Playlist $playlist)
    {
        $this->authorizePlaylist($request, $playlist);

        $playlist->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Playlist deleted successfully',
        ]);
    }

    // Add a song to playlist
    public function addSong(Request $request, Playlist $playlist)
    {
        $this->authorizePlaylist($request, $playlist);

        $validated = $request->validate([
            'song_id' => 'required|exists:songs,id',
        ]);

        $playlist->songs()->syncWithoutDetaching([$validated['song_id']]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Song added to playlist',
        ]);
    }

    // Remove a song from playlist
    public function removeSong(Request $request, Playlist $playlist, Song $song)
    {
        $this->authorizePlaylist($request, $playlist);

        $playlist->songs()->detach($song->id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Song removed from playlist',
        ]);
    }

    // Ensure the playlist belongs to the current user
    private function authorizePlaylist(Request $request, Playlist $playlist): void
    {
        if ($playlist->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }
    }
}
