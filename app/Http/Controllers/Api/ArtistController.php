<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Artist;
use App\Models\Song;

class ArtistController extends Controller
{
    public function apply(Request $request)
{
    $user = $request->user();
    if (!$user) return response()->json(['success'=>false,'message'=>'Unauthorized'],401);

    $v = Validator::make($request->all(), [
        'display_name' => 'required|string|max:255',
        'real_name' => 'nullable|string|max:255',
        'bio' => 'nullable|string|max:2000',
        'country' => 'nullable|string|max:100',
        'profile_image' => 'nullable|string|max:255', // we expect server to store file separately and return path
        'social_links' => 'nullable|array',
    ]);

    if ($v->fails()) {
        return response()->json(['success'=>false, 'errors'=>$v->errors()], 422);
    }

    $data = [
        'display_name' => $request->input('display_name'),
        'real_name' => $request->input('real_name'),
        'country' => $request->input('country'),
        'profile_image' => $request->input('profile_image'),
        'meta' => array_filter([
            'bio' => $request->input('bio'),
        ]),
        'social_links' => $request->input('social_links') ? $request->input('social_links') : null,
    ];

    $artist = Artist::updateOrCreate(
        ['user_id' => $user->id],
        array_merge($data, ['verified' => 0])
    );

    return response()->json([
        'success' => true,
        'data' => $artist
    ], 201);
}


public function me(Request $request)
{
    $user = $request->user();
    $uid = $user ? $user->id : null;
    

    if (!$user) {
        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }

    // find artist record for this user
    $artist = Artist::where('user_id', $user->id)->first();

    if (!$artist) {
        \Log::info('[ArtistController::me] no artist found for user_id: ' . $user->id);
        return response()->json(['status' => 'success', 'data' => ['artist' => null, 'songs' => []]]);
    }

    // Fetch songs explicitly by artist.id to avoid relationship pitfalls
    $songsQuery = Song::where('artist_id', $artist->id)
        ->orderByDesc('created_at');

    $songs = $songsQuery->get()->map(function ($s) {
        return [
            'id' => $s->id,
            'title' => $s->title,
            'status' => $s->status,
            'cover_image_url' => $s->cover_image ? url('storage/' . ltrim($s->cover_image, '/')) : null,
            'file_path' => $s->file_path,
            'audio_url' => url('/api/songs/stream/' . $s->id),
            'created_at' => optional($s->created_at)->toIso8601String(),
        ];
    })->toArray();

    $artistPayload = [
        'id' => $artist->id,
        'user_id' => $artist->user_id,
        'display_name' => $artist->display_name,
        'profile_image' => $artist->profile_image ? url('storage/' . ltrim($artist->profile_image, '/')) : null,
        'verified' => (bool) $artist->verified,
        'meta' => $artist->meta,
    ];

    \Log::info('[ArtistController::me] artist id='.$artist->id.' songs_count='.$songsQuery->count());

    return response()->json([
        'status' => 'success',
        'data' => [
            'artist' => $artistPayload,
            'songs' => $songs,
        ],
    ]);
}


}
