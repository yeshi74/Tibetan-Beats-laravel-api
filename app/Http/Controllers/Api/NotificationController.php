<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::where(
        'user_id',
        $request->user()->id
    )
    ->latest()
    ->get()
    ->filter(function ($notification) {

        if ($notification->type !== 'artist_song') {
            return true;
        }

        $songId = $notification->data['song_id'] ?? null;

        if (!$songId) {
            return false;
        }

        return \App\Models\Song::where('id', $songId)->exists();
    })
    ->values();

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
        ]);
    }

    public function markRead(
        Request $request,
        Notification $notification
    ) {
        if (
            $notification->user_id !==
            $request->user()->id
        ) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $notification->update([
            'is_read' => true,
        ]);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function unreadCount(Request $request)
    {
        $count = Notification::where(
            'user_id',
            $request->user()->id
        )
        ->where('is_read', false)
        ->count();

        return response()->json([
            'status' => 'success',
            'count' => $count,
        ]);
    }


}