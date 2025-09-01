<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dedication;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DedicationController extends Controller
{
    /**
     * GET /api/dedications  (public feed)
     */
    public function index(Request $request)
{
    try {
        $perPage = (int) $request->get('per_page', 20); // default 20
        $page    = (int) $request->get('page', 1);

        $with = [
            'user:id,name,profile_image', 
            'song:id,title,artist,cover_image'
        ];

        if (\Schema::hasColumn('dedications', 'to_user_id')) {
            $with[] = 'toUser:id,name,profile_image';
        }

        $query = \App\Models\Dedication::with($with)->latest();

        $items = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'data'   => $items->getCollection()->map(function ($d) {
                return [
                    'id'              => $d->id,
                    'to_name'         => (string) $d->to_name,
                    'message'         => (string) $d->message,
                    'created_at'      => optional($d->created_at)->toIso8601String(),
                    'cover_image_url' => $d->cover_image_url, // 👈 from Dedication accessor
                    'user'            => $d->user ? [
                        'id'                => $d->user->id,
                        'name'              => $d->user->name,
                        'profile_image_url' => $d->user->profile_image_url,
                    ] : null,
                    'song'            => $d->song ? [
                        'id'              => $d->song->id,
                        'title'           => $d->song->title,
                        'artist'          => $d->song->artist,
                        'cover_image_url' => $d->song->cover_image_url,
                    ] : null,
                    'to_user' => (\Schema::hasColumn('dedications', 'to_user_id') && $d->toUser) ? [
                        'id'                => $d->toUser->id,
                        'name'              => $d->toUser->name,
                        'profile_image_url' => $d->toUser->profile_image_url,
                    ] : null,
                ];
            }),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
            ]
        ]);
    } catch (\Throwable $e) {
        \Log::error('[Dedications] index failed', ['error' => $e->getMessage()]);
        return response()->json([
            'status' => 'error', 
            'message' => 'Failed to fetch dedications'
        ], 500);
    }
}





    /**
     * POST /api/dedications  (auth)
     * Accepts EITHER to_name OR to_user_id
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // validate: require one of to_name or to_user_id
        $validated = $request->validate([
            'song_id'     => 'required|exists:songs,id',
            'message'     => 'required|string|max:300',
            'to_name'     => 'nullable|string|max:80|required_without:to_user_id',
            'to_user_id'  => 'nullable|integer|exists:users,id|required_without:to_name',
        ]);

        try {
            $song = Song::findOrFail($validated['song_id']);

            $toUserId = $validated['to_user_id'] ?? null;
            $toName   = $validated['to_name'] ?? null;

            // If tagging a real user, auto-fill to_name from that user if not provided
            if ($toUserId && !$toName) {
                $to = User::find($toUserId);
                $toName = $to?->name ?? 'Someone special';
            }

            // ================== Dedication Limits ==================
            $countToday = Dedication::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            // Premium detection (both column + role support)
            $isPremium = ($user->is_premium == true || $user->is_premium == 1 || $user->role === 'premium');

            $limit = $isPremium ? 15 : 2;

            if ($countToday >= $limit) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => $isPremium
                        ? 'Your daily premium dedication limit has been reached.'
                        : 'Your daily free dedication limit has been reached. Go Premium for more.',
                    'remaining' => 0,
                ], 403);
            }

            // Build dedication payload
            $data = [
                'user_id' => $user->id,
                'song_id' => $song->id,
                'to_name' => $toName ?? 'Someone',
                'message' => $validated['message'],
            ];

            if ($toUserId && Schema::hasColumn('dedications', 'to_user_id')) {
                $data['to_user_id'] = $toUserId;
            }

            // Save
            $d = Dedication::create($data);

            // Now increment count
            $countToday++;
            $remaining = max($limit - $countToday, 0);

            // Load relations for response
            $with = ['user:id,name,profile_image_url', 'song:id,title,artist'];
            if (Schema::hasColumn('dedications', 'to_user_id')) {
                $with[] = 'toUser:id,name';
            }
            $d->load($with);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Dedication sent successfully',
                'remaining' => $remaining,
                'data'      => [
                    'id'         => $d->id,
                    'to_name'    => $d->to_name,
                    'message'    => $d->message,
                    'created_at' => optional($d->created_at)->toIso8601String(),
                    'cover_image_url' => $d->cover_image_url,
                    'user'       => [
                        'id'                => $d->user->id,
                        'name'              => $d->user->name,
                        'profile_image_url' => $d->user->profile_image_url,
                    ],
                    'song'       => [
                        'id'     => $d->song->id,
                        'title'  => $d->song->title,
                        'artist' => $d->song->artist,
                    ],
                    'to_user'    => (Schema::hasColumn('dedications', 'to_user_id') && $d->toUser) ? [
                        'id'                => $d->toUser->id,
                        'name'              => $d->toUser->name,
                        'profile_image_url' => $d->toUser->profile_image_url,
                    ] : null,
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('[Dedications] store failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Could not save dedication'], 500);
        }
    }




}
