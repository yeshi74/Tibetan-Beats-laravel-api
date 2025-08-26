<?php

namespace App\Events;

use App\Models\Dedication;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DedicationReacted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Dedication $dedication;
    public string $reactionType;

    public function __construct(Dedication $dedication, string $reactionType)
    {
        $this->dedication   = $dedication;
        $this->reactionType = $reactionType;
    }

    public function broadcastAs(): string
    {
        return 'dedication.reacted';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->dedication->id,
            'reaction'        => $this->reactionType,
            'reaction_counts' => $this->dedication->reaction_counts ?? [],
            'total_reactions' => $this->dedication->total_reactions ?? 0,
        ];
    }

    public function broadcastOn(): array
    {
        return [new Channel('dedications')];
    }
}
