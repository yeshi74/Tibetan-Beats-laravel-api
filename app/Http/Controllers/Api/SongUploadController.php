<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SongUploadController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['success'=>false,'message'=>'Unauthorized'],401);

        $v = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'audio' => 'required|file|mimes:mp3,m4a,wav,ogg|max:204800', // max 200MB
            'cover' => 'nullable|image|max:10240',
            'genre_id' => 'nullable|exists:genres,id',
            'artist_id' => 'nullable|exists:artists,id',
            'explicit' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['success'=>false,'errors'=>$v->errors()], 422);
        }

        // ownership check: if artist_id provided, ensure it belongs to current user
        $artistId = $request->input('artist_id');
        if ($artistId) {
            $artist = Artist::find($artistId);
            if (!$artist || $artist->user_id !== $user->id) {
                return response()->json(['success'=>false,'message'=>'Invalid artist ownership'], 403);
            }
        } else {
            // try to find an artist row for user (if exists)
            $artist = Artist::where('user_id', $user->id)->first();
            $artistId = $artist?->id;
        }

        // store files
        $audioPath = $request->file('audio')->store('songs', 'public');
        $coverPath = $request->hasFile('cover') ? $request->file('cover')->store('covers', 'public') : null;

        // create song record in pending state
        $song = Song::create([
            'title' => $request->input('title'),
            'artist' => $artist?->display_name ?? $request->input('artist_name') ?? $request->user()->name,
            'file_path' => $audioPath,
            'cover_image' => $coverPath,
            'genre_id' => $request->input('genre_id'),
            'artist_id' => $artistId,
            'status' => 'pending',
        ]);

        // optionally dispatch job to fetch audio duration / transcode etc.

        return response()->json(['success'=>true, 'data' => $song], 201);
    }
}
