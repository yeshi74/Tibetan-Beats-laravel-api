<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SongClaim;
use Illuminate\Http\Request;

class SongClaimController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'song_title' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string'],
            'song_id' => ['required', 'integer', 'exists:songs,id'],
        ]);

        $user = auth()->user();

        $artist = \App\Models\Artist::where('user_id', $user->id)->first();

        $existing = SongClaim::where('song_id', $validated['song_id'])
            ->where('artist_id', $artist?->id)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already have a pending claim for this song.'
            ], 422);
        }

        $song = \App\Models\Song::findOrFail($validated['song_id']);

        if ($song->artist_id !== null) {
            return response()->json([
                'message' => 'This song already belongs to an artist account.'
            ], 422);
        }

        $claim = SongClaim::create([
            'user_id' => auth()->id(),
            'song_id' => $validated['song_id'] ?? null,
            'artist_id' => $artist?->id,
            'song_title' => $validated['song_title'],
            'claimant_name' => $user->name,
            'email' => $user->email,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Claim submitted successfully',
            'claim' => $claim,
        ]);


    }
}