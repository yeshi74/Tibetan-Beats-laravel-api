<?php

// app/Http/Controllers/Api/DedicationController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dedication;
use App\Models\Song;

class DedicationController extends Controller
{
    // Public feed of recent public dedications
    public function index(Request $request) {
        $items = Dedication::with(['user:id,name', 'song:id,title,artist'])
            ->where('is_public', true)
            ->latest()
            ->take(100)
            ->get()
            ->map(function ($d) {
                return [
                    'id'        => $d->id,
                    'to_name'   => $d->to_name,
                    'message'   => $d->message,
                    'created_at'=> optional($d->created_at)->toIso8601String(),
                    'user'      => ['id' => $d->user->id, 'name' => $d->user->name],
                    'song'      => [
                        'id' => $d->song->id,
                        'title' => $d->song->title,
                        'artist' => $d->song->artist,
                    ],
                ];
            });

        return response()->json(['status'=>'success','data'=>$items]);
    }

    // Public: list dedications for a given song
    public function bySong(Request $request, Song $song) {
        $items = Dedication::with(['user:id,name'])
            ->where('song_id', $song->id)
            ->where('is_public', true)
            ->latest()
            ->take(100)
            ->get()
            ->map(function ($d) use ($song) {
                return [
                    'id'        => $d->id,
                    'to_name'   => $d->to_name,
                    'message'   => $d->message,
                    'created_at'=> optional($d->created_at)->toIso8601String(),
                    'user'      => ['id' => $d->user->id, 'name' => $d->user->name],
                    'song'      => [
                        'id' => $song->id,
                        'title' => $song->title,
                        'artist' => $song->artist,
                    ],
                ];
            });

        return response()->json(['status'=>'success','data'=>$items]);
    }

    // Authenticated: create dedication
    public function store(Request $request) {
        $user = $request->user();
        if (!$user) return response()->json(['message'=>'Unauthorized'], 401);

        $data = $request->validate([
            'song_id'   => 'required|exists:songs,id',
            'to_name'   => 'required|string|max:120',
            'message'   => 'required|string|max:280',
            'is_public' => 'sometimes|boolean',
        ]);

        $d = Dedication::create([
            'user_id'   => $user->id,
            'song_id'   => $data['song_id'],
            'to_name'   => $data['to_name'],
            'message'   => $data['message'],
            'is_public' => $data['is_public'] ?? true,
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id' => $d->id,
                'to_name' => $d->to_name,
                'message' => $d->message,
                'created_at' => optional($d->created_at)->toIso8601String(),
            ]
        ], 201);
    }
}

