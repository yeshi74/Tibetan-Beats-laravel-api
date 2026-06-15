<?php

namespace App\Http\Controllers\Admin;

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
        return view('admin.dashboard', [
            'users' => User::count(),

            'songs' => Song::count(),
            'pendingSongs' => Song::where('status', 'pending')->count(),

            'artists' => Artist::count(),
            'unclaimedArtists' => Artist::whereNull('user_id')->count(),

            'playlists' => Playlist::count(),

            'dedications' => Dedication::count(),

            'claims' => ArtistClaim::count(),
            'pendingClaims' => ArtistClaim::where('status', 'pending')->count(),
        ]);
    }
}