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
        $query = Playlist::withCount('songs')->latest();

        // If admin wants global playlists
        if ($request->query('scope') === 'global') {
            $playlists = $query->whereNull('user_id')->get();
        }
        // User’s own playlists
        else {
            $playlists = $query->where('user_id', $request->user()->id)->get();
        }

        $baseUrl = $request->getSchemeAndHttpHost();

        return response()->json([
            'status'    => 'success',
            'playlists' => $playlists->map(function ($playlist) use ($baseUrl) {
                return [
                    'id'          => $playlist->id,
                    'name'        => $playlist->name,
                    'description' => $playlist->description,
                    'cover_image' => $playlist->cover_image
                        ? $baseUrl . '/storage/' . $playlist->cover_image
                        : null,
                    'is_featured' => $playlist->is_featured,
                    'type'        => $playlist->type,
                    'songs_count' => $playlist->songs_count,
                    'is_admin'    => is_null($playlist->user_id),
                ];
            }),
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
        if ($playlist->user_id) {
            $this->authorizePlaylist($request, $playlist);
        }

        $baseUrl = $request->getSchemeAndHttpHost();

        $songs = $playlist->songs()->latest()->get()->map(function (Song $song) use ($baseUrl) {
            return [
                'id'              => $song->id,
                'title'           => $song->title,
                'artist'          => $song->artist,
                'genre'           => $song->genre,
                'audio_url'       => $baseUrl . '/api/songs/stream/' . $song->id,
                'cover_image_url' => $song->cover_image
                    ? $baseUrl . '/storage/' . $song->cover_image
                    : null,
            ];
        });

        return response()->json([
            'status'   => 'success',
            'playlist' => [
                'id'          => $playlist->id,
                'name'        => $playlist->name,
                'description' => $playlist->description,
                'cover_image' => $playlist->cover_image
                    ? $baseUrl . '/storage/' . $playlist->cover_image
                    : null,
                'is_featured' => $playlist->is_featured,
                'type'        => $playlist->type,
                'songs_count' => $playlist->songs()->count(),
                'is_admin'    => is_null($playlist->user_id),
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


    //admin or global playlist
    // Show featured playlists (admin-curated, visible to all)
    public function featured(Request $request)
    {
        $playlists = Playlist::where('is_featured', true)
            ->withCount('songs')
            ->with('songs') // optional if you want to include songs directly
            ->latest()
            ->get();

        $baseUrl = $request->getSchemeAndHttpHost();

        return response()->json([
            'status'    => 'success',
            'playlists' => $playlists->map(function ($p) use ($baseUrl) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'description' => $p->description,
                    'type'        => $p->type,
                    'is_featured' => $p->is_featured,
                    'cover_image_url' => $p->cover_image ? $baseUrl.'/storage/'.$p->cover_image : null,
                    'songs_count' => $p->songs_count,
                ];
            }),
        ]);
    }

    // Get playlists by type (festival, sleep, curated, etc.)
    public function byType(Request $request, string $type)
    {
        $playlists = Playlist::where('type', $type)
            ->withCount('songs')
            ->latest()
            ->get();

        $baseUrl = $request->getSchemeAndHttpHost();

        return response()->json([
            'status'    => 'success',
            'playlists' => $playlists->map(function ($p) use ($baseUrl) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'description' => $p->description,
                    'cover_image_url' => $p->cover_image ? $baseUrl.'/storage/'.$p->cover_image : null,
                    'songs_count' => $p->songs_count,
                ];
            }),
        ]);
    }

}
