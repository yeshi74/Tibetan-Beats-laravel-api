<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DedicationResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        // $this is App\Models\Dedication
        return [
            'id'              => (int) $this->id,
            'type'            => (string) $this->type,           // flag | lamp | blessing | custom
            'message'         => $this->message,
            'media_url'       => $this->media_url,
            'visibility'      => $this->visibility ?? 'public',
            'is_featured'     => (bool) ($this->is_featured ?? false),
            'reaction_counts' => (array) ($this->reaction_counts ?? []),
            'total_reactions' => (int) ($this->total_reactions ?? 0),
            'song_id'         => $this->song_id ? (int) $this->song_id : null,
            'playlist_id'     => $this->playlist_id ? (int) $this->playlist_id : null,
            'target_user_id'  => $this->target_user_id ? (int) $this->target_user_id : null,
            'status'          => $this->status ?? 'active',
            'created_at'      => optional($this->created_at)->toIso8601String(),
            'expires_at'      => optional($this->expires_at)->toIso8601String(),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id'   => (int) $this->user->id,
                    'name' => (string) $this->user->name,
                ];
            }),
        ];
    }
}
