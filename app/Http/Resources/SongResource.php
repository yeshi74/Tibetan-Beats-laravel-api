<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $baseUrl = $request->getSchemeAndHttpHost();

        return [
            'id' => $this->id,

            'title' => $this->title,

            'artist' => $this->artist,

            'genre' => $this->genre,

            'audio_url' => $baseUrl . '/api/songs/stream/' . $this->id,

            'cover_image_url' => $this->cover_image
                ? $baseUrl . '/storage/' . ltrim($this->cover_image, '/')
                : null,
        ];
    }
}