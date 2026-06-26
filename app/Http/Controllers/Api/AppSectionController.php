<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSection;
use Illuminate\Http\Request;
use App\Models\Song;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Playlist;
use Illuminate\Support\Facades\DB;
use App\Services\SongTransformer;

class AppSectionController extends Controller
{
    public function home(Request $request)
{
    return response()->json([
        'ok' => true,
        'server' => gethostname(),
        'time' => now(),
        'branch' => 'revenuecat-migration',
    ]);
}

    public function search(Request $request)
    {
        $user = $request->user();

        $likedIds = $user
            ? DB::table('liked_songs')
                ->where('user_id', $user->id)
                ->pluck('song_id')
                ->toArray()
            : [];

        $sections = AppSection::with([
            'genre:id,name',
            'playlist:id,name'
        ])
            ->where('page', 'search')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'sections' => $sections->map(function ($section) use ($request, $likedIds) {

                return [

                    'id' => $section->id,
                    'title' => $section->title,
                    'section_type' => $section->section_type,
                    'reference_id' => $section->reference_id,
                    'sort_order' => $section->sort_order,

                    'artists' => $section->section_type === 'verified_artists'
                        ? Artist::where('verified', true)
                            ->where('show_profile', true)
                            ->latest()
                            ->take($section->limit)
                            ->get()
                            ->map(function ($artist) {
                                return [
                                    'id' => $artist->id,
                                    'display_name' => $artist->display_name,
                                    'profile_image' => $artist->profile_image
                                        ? url('storage/' . ltrim($artist->profile_image, '/'))
                                        : null,
                                ];
                            })
                            ->values()
                        : [],

                    'genres' => $section->section_type === 'featured_genres'
                        ? Genre::withCount('songs')
                            ->where('is_featured', true)
                            ->orderBy('name')
                            ->get([
                                'id',
                                'name',
                            ])
                        : [],

                    'playlists' => $section->section_type === 'featured_playlists'
                        ? Playlist::where('is_featured', true)
                            ->get()
                            ->map(function ($playlist) use ($request) {
                                return [
                                    'id' => $playlist->id,
                                    'name' => $playlist->name,
                                    'cover_image_url' => $playlist->cover_image
                                        ? $request->getSchemeAndHttpHost()
                                            . '/storage/'
                                            . ltrim($playlist->cover_image, '/')
                                        : null,
                                ];
                            })
                            ->values()
                        : [],

                    'songs' => $section->section_type === 'playlist' && $section->playlist
                        ? $section->playlist->songs()
                            ->latest()
                            ->take($section->limit)
                            ->get()
                            ->map(fn ($song) => SongTransformer::transform(
                                $request,
                                $song,
                                $likedIds
                            ))
                            ->values()
                        : [],
                ];
            }),
        ]);
    }



}