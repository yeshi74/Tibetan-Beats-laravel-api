<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ArtistClaim;
use Illuminate\Http\Request;

class ArtistClaimController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'claimant_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'reason' => 'required|string|max:5000',
        ]);

        $artist = Artist::findOrFail(
            $validated['artist_id']
        );

        // Already claimed
        if ($artist->user_id) {
            return response()->json([
                'message' => 'This artist profile has already been claimed.'
            ], 422);
        }

        // Existing pending claim
        $pendingClaim = ArtistClaim::where(
            'artist_id',
            $artist->id
        )
        ->where(
            'status',
            'pending'
        )
        ->exists();

        if ($pendingClaim) {
            return response()->json([
                'message' =>
                    'A claim for this artist profile is already under review.'
            ], 422);
        }

        ArtistClaim::create([
            'user_id' => $request->user()->id,
            'artist_id' => $artist->id,
            'claimant_name' => $validated['claimant_name'],
            'email' => $validated['email'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Artist claim submitted successfully.'
        ]);
    }
}