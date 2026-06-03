<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ArtistFollower;
use Illuminate\Http\Request;

class ArtistFollowController extends Controller
{
    public function follow(Request $request,Artist $artist)
    {
        $user = $request->user();

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
        $user = $request->user();

        ArtistFollower::where('user_id',$user->id)->where('artist_id',$artist->id)->delete();

        return response()->json([
            'message' => 'Artist unfollowed',
        ]);
    }


}