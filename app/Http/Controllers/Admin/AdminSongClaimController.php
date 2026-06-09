<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SongClaim;

class AdminSongClaimController extends Controller
{
    public function index()
    {
        $claims = SongClaim::latest()->paginate(20);

        return view('admin.song-claims.index', compact('claims'));
    }

    public function show(SongClaim $claim)
    {
        return view('admin.song-claims.show',compact('claim'));
    }

    public function approve(SongClaim $claim)
    {
        if (!$claim->artist_id) {
            return back()->with(
                'error',
                'No artist account linked to this claim.'
            );
        }

        $song = null;

        if ($claim->song_id) {
            $song = \App\Models\Song::find($claim->song_id);
        }

        if (!$song) {
            $song = \App\Models\Song::where(
                'title',
                $claim->song_title
            )->first();
        }

        if (!$song) {
            return back()->with(
                'error',
                'Song not found.'
            );
        }

        $song->artist_id = $claim->artist_id;
        $song->save();

        $claim->update([
            'status' => 'approved',
            'admin_notes' =>
                'Ownership transferred successfully.',
        ]);

        return back()->with(
            'success',
            'Ownership transferred successfully.'
        );
    }

    public function reject(Request $request, SongClaim $claim)
    {
        $claim->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with(
            'success',
            'Ownership claim rejected.'
        );
    }


}
