<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Song;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Dedication;
use App\Models\ArtistClaim;

class AdminHomeController extends Controller
{
    public function index()
    {
        return view('admin.spa-dashboard', [

            // Main Stats
            'users' => User::count(),
            'songs' => Song::count(),
            'artists' => Artist::count(),
            'playlists' => Playlist::count(),

            // Action Items
            'pendingSongs' => Song::where('status', 'pending')->count(),
            'pendingClaims' => ArtistClaim::where('status', 'pending')->count(),
            'dedications' => Dedication::count(),
            'unclaimedArtists' => Artist::whereNull('user_id')->count(),

            // System Health
            'songsMissingCover' => Song::whereNull('cover_image')
                ->orWhere('cover_image', '')
                ->count(),

            'songsMissingAudio' => Song::whereNull('file_path')
                ->orWhere('file_path', '')
                ->count(),

            'emptyPlaylists' => Playlist::doesntHave('songs')->count(),

            'totalPlays' => Song::sum('play_count'),

            'mostPlayedSong' => Song::with('artist')
                ->orderByDesc('play_count')
                ->first(),

            'topPlayedSongs' => Song::with('artist')
                ->orderByDesc('play_count')
                ->take(5)
                ->get(),

        ]);
    }
}