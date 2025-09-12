<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        $artist = Artist::where('user_id', $user->id)->first();
        return response()->json(['success'=>true, 'data'=>$artist]);
    }
}
