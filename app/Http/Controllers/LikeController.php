<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function like(Request $request, Song $song) {
        $user = $request->user();
        $user->likedSongs()->syncWithoutDetaching([$song->id]);
        return response()->json([
            'status' => 'success',
            'liked' => true,
            'song_id' => $song->id,
        ], 200);
    }

    public function unlike(Request $request, Song $song) {
        $user = $request->user();
        $user->likedSongs()->detach($song->id);
        return response()->json([
            'status' => 'success',
            'liked' => false,
            'song_id' => $song->id,
        ], 200);
    }

    // Optional: list liked songs
    public function index(Request $request) {
        $songs = $request->user()->likedSongs()->latest('liked_songs.created_at')->get();
        return response()->json([
            'status' => 'success',
            'songs' => $songs,
        ]);
    }
}