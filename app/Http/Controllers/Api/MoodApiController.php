<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mood;
use App\Services\SongTransformer;
use Illuminate\Support\Facades\DB;

class MoodApiController extends Controller
{

    public function songs(Request $request, Mood $mood)
    {
        $user = $this->resolveUser($request);

        $likedIds = $user
            ? DB::table('liked_songs')
                ->where('user_id', $user->id)
                ->pluck('song_id')
                ->toArray()
            : [];

        $songs = $mood->songs()
            ->visible()
            ->where('status', 'published')
            ->latest()
            ->get()
            ->map(fn ($song) => SongTransformer::transform(
                $request,
                $song,
                $likedIds
            ));

        return response()->json([
            'status' => 'success',
            'data' => $songs,
        ]);
    }

    public function index()
    {
        $moods = Mood::where('is_active', true)
            ->withCount([
                'songs' => function ($query) {
                    $query->visible()
                        ->where('status', 'published');
                }
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'emoji',
                'sort_order',
                'start_color',
                'end_color',
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $moods,
        ]);
    }

    protected function resolveUser(Request $request)
    {
        $user = $request->user();
        if ($user) return $user;

        try {
            return auth('sanctum')->user();
        } catch (\Throwable $e) {
            return null;
        }
    }

}
