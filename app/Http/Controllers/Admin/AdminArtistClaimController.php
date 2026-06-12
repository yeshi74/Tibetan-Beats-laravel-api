<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtistClaim;
use App\Models\Role;
use Illuminate\Http\Request;

class AdminArtistClaimController extends Controller
{
    public function index()
    {
        $claims = ArtistClaim::with([
            'artist',
            'user',
        ])
        ->latest()
        ->paginate(20);

        return view(
            'admin.artist-claims.index',
            compact('claims')
        );
    }

    public function show(ArtistClaim $claim)
    {
        return view(
            'admin.artist-claims.show',
            compact('claim')
        );
    }

    public function approve(ArtistClaim $claim)
    {
        $artist = $claim->artist;
        $user = $claim->user;

        if (!$artist || !$user) {
            return back()->with(
                'error',
                'Artist or user not found.'
            );
        }

        $artist->user_id = $user->id;
        $artist->verified = true;
        $artist->save();

        $artistRoleId = Role::where(
            'name',
            'artist'
        )->value('id');

        if ($artistRoleId) {
            $user->role_id = $artistRoleId;
            $user->save();
        }

        $claim->update([
            'status' => 'approved',
            'admin_notes' =>
                'Artist profile ownership transferred.',
        ]);

        return back()->with(
            'success',
            'Artist claim approved.'
        );
    }

    public function reject(
        Request $request,
        ArtistClaim $claim
    ) {
        $claim->update([
            'status' => 'rejected',
            'admin_notes' =>
                $request->admin_notes,
        ]);

        return back()->with(
            'success',
            'Artist claim rejected.'
        );
    }
}