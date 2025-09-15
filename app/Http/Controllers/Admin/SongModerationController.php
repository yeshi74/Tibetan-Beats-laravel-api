<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;

class SongModerationController extends Controller
{
    public function approve(Request $request, Song $song)
    {
        // TODO: add proper auth/permission middleware for admin only
        $song->status = 'published';
        $song->is_published = true;
        $song->save();

        // optionally notify artist (email/notification)
        // dispatch(new NotifyArtistSongApproved($song));

        return response()->json(['status' => 'success', 'message' => 'Song published', 'data' => $song]);
    }

    public function reject(Request $request, Song $song)
    {
        $reason = $request->input('reason');
        $song->status = 'rejected';
        $song->is_published = false;
        // optionally save rejection reason in a column if you have one:
        if (\Schema::hasColumn('songs', 'reject_reason')) {
            $song->reject_reason = $reason;
        }
        $song->save();

        // notify artist about rejection -> dispatch job

        return response()->json(['status' => 'success', 'message' => 'Song rejected']);
    }
}
