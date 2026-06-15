<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Song;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Dedication;
use App\Models\ArtistClaim;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'users' => User::count(),

            'songs' => Song::count(),
            'published_songs' => Song::where('status', 'published')->count(),
            'pending_songs' => Song::where('status', 'pending')->count(),

            'artists' => Artist::count(),
            'claimed_artists' => Artist::whereNotNull('user_id')->count(),
            'unclaimed_artists' => Artist::whereNull('user_id')->count(),

            'playlists' => Playlist::count(),

            'dedications' => Dedication::count(),

            'claims' => ArtistClaim::count(),
            'pending_claims' => ArtistClaim::where('status', 'pending')->count(),

            'songs_missing_cover' => Song::whereNull('cover_image')
                ->orWhere('cover_image', '')
                ->count(),

            'songs_missing_audio' => Song::whereNull('file_path')
                ->orWhere('file_path', '')
                ->count(),

            'empty_playlists' => Playlist::doesntHave('songs')->count(),
        ]);
    }
}