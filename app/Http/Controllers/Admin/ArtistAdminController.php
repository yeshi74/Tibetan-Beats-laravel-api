<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ArtistVerificationUpdated;

class ArtistAdminController extends Controller
{
    // GET /admin/artists
    public function index(Request $request)
    {
        // show pending first
        $q = Artist::with('user')->orderBy('verified')->latest();

        if ($request->filled('filter') && $request->filter === 'verified') {
            $q->where('verified', true);
        } elseif ($request->filled('filter') && $request->filter === 'pending') {
            $q->where('verified', false);
        }

        $artists = $q->paginate(20);

        return view('admin.artists.index', compact('artists'));
    }

    // GET /admin/artists/{artist}
    public function show(\App\Models\Artist $artist)
    {
        return view('admin.artists.show', compact('artist'));
    }

    // PATCH /admin/artists/{artist}/approve
// PATCH /admin/artists/{artist}/approve
public function approve(Request $request, Artist $artist)
{
    if ($artist->verified) {
        return redirect()->back()->with('info', 'Already verified.');
    }

    $artist->verified = true;
    $artist->save();

    // Update user role_id
    $user = $artist->user;
    if ($user) {
        $artistRoleId = \App\Models\Role::where('name', 'artist')->value('id');
        if ($artistRoleId && $user->role_id !== $artistRoleId) {
            $user->role_id = $artistRoleId;
            $user->save();
        }
    }

    // Notify artist by email
    try {
        Mail::to($artist->user->email)->send(new ArtistVerificationUpdated($artist, true));
    } catch (\Throwable $e) {
        \Log::error('[ArtistAdmin] mail failed: '.$e->getMessage());
    }

    return redirect()->route('admin.artists.show', $artist)->with('success', 'Artist approved.');
}


    // PATCH /admin/artists/{artist}/reject
    public function reject(Request $request, Artist $artist)
    {
        $reason = $request->input('reason', null);

        $artist->verified = false;
        $meta = $artist->meta ?? [];
        $meta['last_rejection_reason'] = $reason;
        $artist->meta = $meta;
        $artist->save();

        try {
            Mail::to($artist->user->email)->send(new ArtistVerificationUpdated($artist, false, $reason));
        } catch (\Throwable $e) {
            \Log::error('[ArtistAdmin] mail-failed: '.$e->getMessage());
        }

        return redirect()->route('admin.artists.show', $artist)->with('success', 'Artist rejected.');
    }
}
