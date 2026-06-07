<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dedication;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class DedicationController extends Controller
{
    /**
     * GET /api/dedications (public feed with filters)
     */
    public function index(Request $request)
    {

        $sort = $request->get('sort', 'latest');

        try {
            $filter = $request->get('filter', 'all'); // all | my | received
            $user   = $request->user();

            $with = [
                'user:id,name,profile_image',
                'song:id,title,artist,cover_image',
            ];
            if (Schema::hasColumn('dedications', 'to_user_id')) {
                $with[] = 'toUser:id,name,profile_image';
            }

            $query = Dedication::with($with)->withCount('likes');
            if ($sort === 'popular') {
                $query->orderByDesc('likes_count')
                      ->orderByDesc('created_at');
            } else {
                $query->latest();
            }

            // ✅ Visibility rules
            if ($filter === 'all') {
                // Public feed: approved only
                $query->approved();
            }

            // ✅ Filters
            if ($request->filled('song_id')) {
                $query->where('song_id', (int) $request->input('song_id'));
            }
            if ($request->filled('user_id')) {
                $query->where('user_id', (int) $request->input('user_id'));
            }

            if ($filter === 'my' && $user) {
                $query->where('user_id', $user->id);
            } elseif ($filter === 'received' && $user) {
                $query->where(function ($q) use ($user) {
                    $q->where('to_user_id', $user->id)
                      ->orWhere('to_name', $user->name);
                });
            } elseif ($filter === 'received' && !$user) {
                $query->whereRaw('0=1');
            }

            $perPage = (int) $request->get('per_page', 20);
            $page    = (int) $request->get('page', 1);

            $items = $query->paginate($perPage, ['*'], 'page', $page);

            // ✅ Counts
            $counts = [
                'all'      => Dedication::approved()->count(),
                'my'       => $user ? Dedication::where('user_id', $user->id)->count() : 0,
                'received' => $user ? Dedication::where(function ($q) use ($user) {
                    $q->where('to_user_id', $user->id)
                      ->orWhere('to_name', $user->name);
                })->count() : 0,
            ];

            return response()->json([
                'status' => 'success',
                'filter' => $filter,
                'data'   => $items->getCollection()->map(function ($d) use ($user) {
                    return [
                        'id'              => $d->id,
                        'to_name'         => (string) $d->to_name,
                        'message'         => (string) $d->message,
                        'status'          => (string) $d->status, // 👈 moderation status
                        'created_at'      => optional($d->created_at)->toIso8601String(),
                        'cover_image_url' => $d->cover_image_url,
                        'user' => $d->user ? [
                            'id'                => $d->user->id,
                            'name'              => $d->user->name,
                            'profile_image_url' => $d->user->profile_image_url,
                        ] : null,
                        'song' => $d->song ? [
                            'id'              => $d->song->id,
                            'title'           => $d->song->title,
                            'artist'          => $d->song->artist,
                            'cover_image_url' => $d->song->cover_image_url,
                        ] : null,
                        'to_user' => $d->toUser ? [
                            'id'                => $d->toUser->id,
                            'name'              => $d->toUser->name,
                            'profile_image_url' => $d->toUser->profile_image_url,
                        ] : null,
                        'likes_count' => $d->likes_count,
                        'is_liked'    => $user ? $d->likedBy($user) : false,
                    ];
                }),
                'meta' => [
                    'current_page' => $items->currentPage(),
                    'last_page'    => $items->lastPage(),
                    'per_page'     => $items->perPage(),
                    'total'        => $items->total(),
                    'counts'       => $counts,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('[Dedications] index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch dedications'
            ], 500);
        }
    }

    /**
     * POST /api/dedications
     */
        public function store(Request $request)
    {
        $user = $request->user()?->fresh();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Basic validation first (no exists rule), validate types/length only
        $validated = $request->validate([
            'song_id'     => ['required'],
            'message'     => ['required', 'string', 'max:300'],
            'to_name'     => ['nullable', 'string', 'max:80', 'required_without:to_user_id'],
            'to_user_id'  => ['nullable', 'integer', 'exists:users,id', 'required_without:to_name'],
        ]);

        // Normalize song id: sometimes client may send string, null, etc.
        $rawSongId = $validated['song_id'];
        $song = null;

        // Try integer id
        if (is_numeric($rawSongId)) {
            $id = (int) $rawSongId;
            if ($id > 0) {
                $song = Song::find($id);
            }
        }

        // If not found, try other fallbacks if your songs table has alternate keys:
        //  - legacy_id (if you imported songs and used legacy ids)
        //  - uuid (if you keep uuid column)
        //  - slug or external_id as last resort
        if (!$song) {
            // fallback: try an exact UUID/slug lookup if the client sent a non-numeric string
            if (is_string($rawSongId) && !is_numeric($rawSongId)) {
                // try uuid
                if (Schema::hasColumn('songs', 'uuid')) {
                    $song = Song::where('uuid', $rawSongId)->first();
                }
                // try external id/legacy columns
                if (!$song && Schema::hasColumn('songs', 'legacy_id')) {
                    $song = Song::where('legacy_id', $rawSongId)->first();
                }
                if (!$song && Schema::hasColumn('songs', 'slug')) {
                    $song = Song::where('slug', $rawSongId)->first();
                }
            }
        }

        // Final check: if still not found return clear 422
        if (!$song) {
            // Return a better error payload the client can use for debug
            return response()->json([
                'success' => false,
                'status'  => 422,
                'message' => 'The provided song_id does not correspond to any song.',
                'detail'  => [
                    'received_song_id' => $rawSongId,
                    'hint' => 'Ensure the client sends the numeric DB id for the song, or a valid uuid/legacy id if supported.',
                ],
                'raw' => [],
            ], 422);
        }

        try {
            $toUserId = $validated['to_user_id'] ?? null;
            $toName   = $validated['to_name'] ?? null;

            if ($toUserId && Schema::hasColumn('dedications', 'to_user_id')) {
                $recipient = User::find($toUserId);
                $toName = $recipient?->name ?? $toName ?? 'Someone special';
            }

            // Dedication Limits
            $countToday = Dedication::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            $isPremium = (bool) $user->is_premium || $user->role === 'premium';
            $limit     = $isPremium ? null : 2;

            if (!$isPremium && $limit !== null && $countToday >= $limit) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Your daily free dedication limit has been reached. Go Premium for unlimited dedications.',
                    'remaining' => 0,
                ], 403);
            }

            $d = Dedication::create([
                'user_id'    => $user->id,
                'song_id'    => $song->id,
                'to_name'    => $toName ?? 'Someone',
                'message'    => $validated['message'],
                'status'     => 'pending',
                'to_user_id' => $toUserId,
            ]);

            $countToday++;
            $remaining = $limit ? max($limit - $countToday, 0) : null;

            $with = ['user:id,name,profile_image_url', 'song:id,title,artist,cover_image'];
            if (Schema::hasColumn('dedications', 'to_user_id')) {
                $with[] = 'toUser:id,name,profile_image_url';
            }

            $d->load($with)->loadCount('likes');

            return response()->json([
                'status'    => 'success',
                'message'   => 'Your dedication was sent and will appear publicly after approval.',
                ...( !$isPremium ? ['remaining' => $remaining] : [] ),
                'moderation'=> 'pending',
                'data'      => [
                    'id'              => $d->id,
                    'to_name'         => $d->to_name,
                    'message'         => $d->message,
                    'created_at'      => optional($d->created_at)->toIso8601String(),
                    'cover_image_url' => $d->cover_image_url,
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
                    'to_user'         => ($d->toUser) ? [
                        'id'                => $d->toUser->id,
                        'name'              => $d->toUser->name,
                        'profile_image_url' => $d->toUser->profile_image_url,
                    ] : null,
                    'likes_count' => $d->likes_count,
                    'is_liked'    => false,
                ],
            ], 201);

        } catch (\Throwable $e) {
            Log::error('[Dedications] store failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Could not save dedication'], 500);
        }
    }

    public function like(Request $request, Dedication $dedication)
    {
        $user = $request->user();
        $dedication->likes()->firstOrCreate(['user_id' => $user->id]);

        return response()->json([
            'status'      => 'success',
            'likes_count' => $dedication->likes()->count(),
        ]);
    }

    public function unlike(Request $request, Dedication $dedication)
    {
        $user = $request->user();
        $dedication->likes()->where('user_id', $user->id)->delete();

        return response()->json([
            'status'      => 'success',
            'likes_count' => $dedication->likes()->count(),
        ]);
    }

    public function bySong(Song $song)
    {
        return Dedication::query()
            ->where('song_id', $song->id)
            ->approved()
            ->latest()
            ->paginate((int) request('per_page', 20));
    }
}
