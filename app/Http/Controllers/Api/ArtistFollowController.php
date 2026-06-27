<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ArtistFollower;
use Illuminate\Http\Request;
use App\Services\SongTransformer;

class ArtistFollowController extends Controller
{
    public function follow(Request $request,Artist $artist)
    {
        $user = $this->resolveUser($request);

        if ($artist->user_id === $user->id) {
            return response()->json([
                'message' => 'You cannot follow yourself',
            ], 422);
        }

        ArtistFollower::firstOrCreate([
            'user_id' => $user->id,
            'artist_id' => $artist->id,
        ]);

        return response()->json([
            'message' => 'Artist followed',
        ]);
    }

    public function unfollow(Request $request,Artist $artist)
    {
        $user = $this->resolveUser($request);

        ArtistFollower::where('user_id',$user->id)->where('artist_id',$artist->id)->delete();

        return response()->json([
            'message' => 'Artist unfollowed',
        ]);
    }

    protected function resolveUser(Request $request)
    {
        $user = $request->user();
        if ($user) {
            return $user;
        }

        try {
            return auth('sanctum')->user();
        } catch (\Throwable $e) {
            return null;
        }
    }


}