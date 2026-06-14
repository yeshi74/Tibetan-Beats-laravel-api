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

class AppSectionController extends Controller
{
    public function home(Request $request)
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
            ->where('page', 'home')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'title',
                'section_type',
                'reference_id',
                'sort_order',
            ]);

        return response()->json([
            'sections' => $sections->map(function ($section) use ($request, $likedIds) {

                return [
                    'id' => $section->id,
                    'title' => $section->title,
                    'section_type' => $section->section_type,
                    'reference_id' => $section->reference_id,
                    'sort_order' => $section->sort_order,

                    'genre' => $section->genre ? [
                        'id' => $section->genre->id,
                        'name' => $section->genre->name,
                    ] : null,

                    'playlist' => $section->playlist ? [
                        'id' => $section->playlist->id,
                        'name' => $section->playlist->name,
                    ] : null,

                    'artists' => $section->section_type === 'popular_artists'
                        ? Artist::withCount('followers')
                            ->where('verified', true)
                            ->orderByDesc('followers_count')
                            ->take($section->limit)
                            ->get()
                            ->map(function ($artist) {
                                return [
                                    'id' => $artist->id,
                                    'display_name' => $artist->display_name,
                                    'verified' => (bool) $artist->verified,
                                    'followers_count' => $artist->followers_count,
                                    'profile_image' => $artist->profile_image
                                        ? url('storage/' . ltrim($artist->profile_image, '/'))
                                        : null,
                                ];
                            })
                            ->values()
                        : [],

                    'songs' => match ($section->section_type) {

                        'playlist' => $section->playlist
                            ? $section->playlist->songs()
                                ->latest()
                                ->take($section->limit)
                                ->get()
                                ->map(fn ($song) => $this->transformSong($request, $song, $likedIds))
                                ->values()
                            : [],

                        'genre' => $section->genre
                            ? Song::where('genre_id', $section->genre->id)
                                ->latest()
                                ->take($section->limit)
                                ->get()
                                ->map(fn ($song) => $this->transformSong($request, $song, $likedIds))
                                ->values()
                            : [],

                        'latest_songs' => Song::latest()
                            ->take($section->limit)
                            ->get()
                            ->map(fn ($song) => $this->transformSong($request, $song, $likedIds))
                            ->values(),

                        'trending_songs' => Song::orderByDesc('play_count')
                            ->take($section->limit)
                            ->get()
                            ->map(fn ($song) => $this->transformSong($request, $song, $likedIds))
                            ->values(),

                        'most_liked_songs' => Song::select('songs.*')
                            ->leftJoin('liked_songs', 'songs.id', '=', 'liked_songs.song_id')
                            ->selectRaw('COUNT(liked_songs.id) as likes_count')
                            ->groupBy('songs.id')
                            ->orderByDesc('likes_count')
                            ->take($section->limit)
                            ->get()
                            ->map(fn ($song) => $this->transformSong($request, $song, $likedIds))
                            ->values(),

                        default => [],
                    },
                ];
            }),
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
                            ->map(fn ($song) => $this->transformSong(
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

    protected function transformSong(
        Request $request,
        Song $song,
        array $likedIds = []
    ): array
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $isLiked = in_array($song->id, $likedIds);

        return [
            'id' => $song->id,
            'title' => $song->title,
            'artist' => $song->artist,
            'artist_id' => $song->artist_id,
            'genre_id' => $song->genre_id,
            'genre' => $song->genre ? $song->genre->name : null,
            'audio_url' => $baseUrl . '/api/songs/stream/' . $song->id,
            'cover_image_url' => $song->cover_image
                ? $baseUrl . '/storage/' . ltrim($song->cover_image, '/')
                : null,
            'created_at' => optional($song->created_at)->toIso8601String(),
            'is_liked' => (bool) $isLiked,
            'liked' => $isLiked ? 1 : 0,
            'status' => $song->status,
            'rejection_reason' => $song->rejection_reason,
        ];
    }

}