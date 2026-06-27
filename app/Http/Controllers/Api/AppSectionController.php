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
    $user = $request->user();

    // 1. Fetch liked IDs efficiently
    $likedIds = $user
        ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
        : [];

    // 2. Fetch only necessary sections
    $sections = AppSection::with([
        'genre:id,name',
        'playlist:id,name'
    ])
        ->where('page', 'home')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get(['id', 'title', 'section_type', 'reference_id', 'sort_order', 'limit']);

    // 3. Process sections
    $response = $sections->map(function ($section) use ($request, $likedIds) {
        $artists = [];
        $songs = [];
        $limit = $section->limit ?? 10; // Fallback limit to prevent un-bounded queries

        switch ($section->section_type) {
            case 'popular_artists':
                $artists = Artist::where('show_profile', true)
                    ->where('verified', true)
                    ->withCount('followers')
                    ->orderByDesc('followers_count')
                    ->take($limit)
                    ->get(['id', 'display_name', 'verified', 'profile_image']) // Only fetch needed fields
                    ->map(fn($artist) => [
                        'id' => $artist->id,
                        'display_name' => $artist->display_name,
                        'verified' => (bool)$artist->verified,
                        'followers_count' => $artist->followers_count,
                        'profile_image' => $artist->profile_image 
                            ? url('storage/' . ltrim($artist->profile_image, '/')) 
                            : null,
                    ])
                    ->values();
                break;

            case 'playlist':
                if ($section->playlist) {
                    $songs = $section->playlist->songs()
                        ->latest()
                        ->take($limit)
                        ->get()
                        ->map(fn($song) => SongTransformer::transform($request, $song, $likedIds))
                        ->values();
                }
                break;

            case 'genre':
                if ($section->genre) {
                    $songs = Song::where('genre_id', $section->genre->id)
                        ->latest()
                        ->take($limit)
                        ->get()
                        ->map(fn($song) => SongTransformer::transform($request, $song, $likedIds))
                        ->values();
                }
                break;

            case 'latest_songs':
                $songs = Song::latest()
                    ->take($limit)
                    ->get()
                    ->map(fn($song) => SongTransformer::transform($request, $song, $likedIds))
                    ->values();
                break;

            case 'trending_songs':
                $songs = Song::orderByDesc('play_count')
                    ->take($limit)
                    ->get()
                    ->map(fn($song) => SongTransformer::transform($request, $song, $likedIds))
                    ->values();
                break;

            case 'most_liked_songs':
                // Optimized to avoid strict mode groupBy execution hangs
                $songs = Song::withCount('likes')
                    ->orderByDesc('likes_count')
                    ->take($limit)
                    ->get()
                    ->map(fn($song) => SongTransformer::transform($request, $song, $likedIds))
                    ->values();
                break;
        }

        return [
            'id' => $section->id,
            'title' => $section->title,
            'section_type' => $section->section_type,
            'reference_id' => $section->reference_id,
            'sort_order' => $section->sort_order,
            'genre' => $section->genre ? ['id' => $section->genre->id, 'name' => $section->genre->name] : null,
            'playlist' => $section->playlist ? ['id' => $section->playlist->id, 'name' => $section->playlist->name] : null,
            'artists' => $artists,
            'songs' => $songs,
        ];
    });

    return response()->json([
        'sections' => $response,
    ]);
}



    // public function home(Request $request)
    // {

    //     $user = $request->user();

    //     $likedIds = $user
    //         ? DB::table('liked_songs')
    //             ->where('user_id', $user->id)
    //             ->pluck('song_id')
    //             ->toArray()
    //         : [];

    //     $sections = AppSection::with([
    //         'genre:id,name',
    //         'playlist:id,name'
    //     ])
    //         ->where('page', 'home')
    //         ->where('is_active', true)
    //         ->orderBy('sort_order')
    //         ->get([
    //             'id',
    //             'title',
    //             'section_type',
    //             'reference_id',
    //             'sort_order',
    //         ]);

    //     return response()->json([
    //         'sections' => $sections->map(function ($section) use ($request, $likedIds) {

    //             return [
    //                 'id' => $section->id,
    //                 'title' => $section->title,
    //                 'section_type' => $section->section_type,
    //                 'reference_id' => $section->reference_id,
    //                 'sort_order' => $section->sort_order,

    //                 'genre' => $section->genre ? [
    //                     'id' => $section->genre->id,
    //                     'name' => $section->genre->name,
    //                 ] : null,

    //                 'playlist' => $section->playlist ? [
    //                     'id' => $section->playlist->id,
    //                     'name' => $section->playlist->name,
    //                 ] : null,

    //                 'artists' => $section->section_type === 'popular_artists'
    //                     ? Artist::withCount('followers')
    //                         ->where('show_profile', true)
    //                         ->where('verified', true)
    //                         ->orderByDesc('followers_count')
    //                         ->take($section->limit)
    //                         ->get()
    //                         ->map(function ($artist) {
    //                             return [
    //                                 'id' => $artist->id,
    //                                 'display_name' => $artist->display_name,
    //                                 'verified' => (bool) $artist->verified,
    //                                 'followers_count' => $artist->followers_count,
    //                                 'profile_image' => $artist->profile_image
    //                                     ? url('storage/' . ltrim($artist->profile_image, '/'))
    //                                     : null,
    //                             ];
    //                         })
    //                         ->values()
    //                     : [],

    //                 'songs' => match ($section->section_type) {

    //                     'playlist' => $section->playlist
    //                         ? $section->playlist->songs()
    //                             ->latest()
    //                             ->take($section->limit)
    //                             ->get()
    //                             ->map(fn ($song) => SongTransformer::transform($request, $song, $likedIds))
    //                             ->values()
    //                         : [],

    //                     'genre' => $section->genre
    //                         ? Song::where('genre_id', $section->genre->id)
    //                             ->latest()
    //                             ->take($section->limit)
    //                             ->get()
    //                             ->map(fn ($song) => SongTransformer::transform($request, $song, $likedIds))
    //                             ->values()
    //                         : [],

    //                     'latest_songs' => Song::latest()
    //                         ->take($section->limit)
    //                         ->get()
    //                         ->map(fn ($song) => SongTransformer::transform($request, $song, $likedIds))
    //                         ->values(),

    //                     'trending_songs' => Song::orderByDesc('play_count')
    //                         ->take($section->limit)
    //                         ->get()
    //                         ->map(fn ($song) => SongTransformer::transform($request, $song, $likedIds))
    //                         ->values(),

    //                     'most_liked_songs' => Song::select('songs.*')
    //                         ->leftJoin('liked_songs', 'songs.id', '=', 'liked_songs.song_id')
    //                         ->selectRaw('COUNT(liked_songs.id) as likes_count')
    //                         ->groupBy('songs.id')
    //                         ->orderByDesc('likes_count')
    //                         ->take($section->limit)
    //                         ->get()
    //                         ->map(fn ($song) => SongTransformer::transform($request, $song, $likedIds))
    //                         ->values(),

    //                     default => [],
    //                 },
    //             ];
    //         }),
    //     ]);
    // }

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