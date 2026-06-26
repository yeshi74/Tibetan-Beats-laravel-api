<?php

namespace App\Services;

use App\Models\Song;
use Illuminate\Http\Request;

class SongTransformer
{
    public static function transform(
        Request $request,
        Song $song,
        array $likedIds = [],
        array $extra = []
    ): array {
        $baseUrl = $request->getSchemeAndHttpHost();

        $isLiked = in_array(
            $song->id,
            $likedIds,
            true
        );

        $data = [
            'id' => $song->id,

            'title' => $song->title,

            'artist' => $song->artist,

            'artist_id' => $song->artist_id,

            'genre_id' => $song->genre_id,

            'genre' => $song->genre?->name,

            'audio_url' => $baseUrl . '/api/songs/stream/' . $song->id,

            'cover_image_url' => $song->cover_image
                ? $baseUrl . '/storage/' . ltrim($song->cover_image, '/')
                : null,

            'created_at' => optional($song->created_at)->toIso8601String(),

            'play_count' => (int) ($song->play_count ?? 0),

            'is_liked' => (bool) $isLiked,

            'liked' => $isLiked ? 1 : 0,

            'status' => $song->status,

            'rejection_reason' => $song->rejection_reason,
        ];

        return array_merge($data, $extra);
    }
}