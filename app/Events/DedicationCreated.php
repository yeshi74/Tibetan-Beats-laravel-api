<?php

namespace App\Events;

use App\Models\Dedication;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DedicationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Dedication $dedication;

    /**
     * Create a new event instance.
     */
    public function __construct(Dedication $dedication)
    {
        $this->dedication = $dedication;
    }

    public function broadcastAs(): string
    {
        return 'dedication.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->dedication->id,
            'type'            => $this->dedication->type,
            'message'         => $this->dedication->message,
            'media_url'       => $this->dedication->media_url,
            'is_featured'     => $this->dedication->is_featured,
            'reaction_counts' => $this->dedication->reaction_counts ?? [],
            'total_reactions' => $this->dedication->total_reactions ?? 0,
            'created_at'      => $this->dedication->created_at->toIso8601String(),
            'user'            => [
                'id'   => $this->dedication->user->id,
                'name' => $this->dedication->user->name,
            ],
        ];
    }

    public function broadcastOn(): array
    {
        return [new Channel('dedications')];
    }
}
