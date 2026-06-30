<?php

namespace App\Jobs;

use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class SendAdminNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public AdminNotification $notification;

    public function __construct(AdminNotification $notification)
    {
        $this->notification = $notification;
    }

    public function handle(): void
    {
        // Resolve audience
        $audience = $this->notification->audience; // e.g. 'all', 'role:admin', 'users:[1,2]'
        $query = User::query();

        if ($audience === 'all') {
            // no where, send to all users
        } elseif (str_starts_with($audience, 'role:')) {
            $role = substr($audience, strlen('role:'));
            $query->whereHas('role', fn($q) => $q->where('name', $role));
        } elseif (str_starts_with($audience, 'users:')) {
            $ids = json_decode(substr($audience, strlen('users:')), true) ?: [];
            $query->whereIn('id', $ids);
        } elseif ($audience === 'premium') {
            $query->where('is_premium', true);
        } else {
            // Fallback: treat as all
        }

        $users = $query->whereNotNull('device_token')->pluck('device_token')->filter()->unique()->values();

        $payload = [
            'title' => $this->notification->title,
            'body'  => $this->notification->body,
            'data'  => (array) $this->notification->payload,
        ];

        // TODO: integrate your FCM client here. For now we log and pretend to send.
        foreach ($users as $token) {
            // Replace this with real FCM call (e.g., using laravel-notification-channels/fcm or custom HTTP)
            Log::info('[Notify] would send to token', ['token' => $token, 'payload' => $payload]);
        }

        // Update counters
        $this->notification->update([
            'sent_count' => $users->count(),
            'sent_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        
        // increment attempts or store details
        $this->notification->increment('attempts');
    }
}
