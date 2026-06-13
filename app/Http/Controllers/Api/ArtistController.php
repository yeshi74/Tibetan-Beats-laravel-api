<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Artist;
use App\Models\Song;
use App\Models\ArtistFollower;

class ArtistController extends Controller
{
    // app/Http/Controllers/Api/ArtistController.php (method apply)
public function apply(Request $request)
{
    $user = $request->user();
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    // Validation: include file rules for ID front/back and optional avatar
    $v = Validator::make($request->all(), [
        'display_name' => 'required|string|max:255',
        'real_name' => 'nullable|string|max:255',
        'bio' => 'nullable|string|max:2000',
        'country' => 'nullable|string|max:100',
        'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        'id_front' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        'id_back' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        'social_links' => 'nullable|array',
        'sample_track' => 'nullable|mimes:mp3,wav,m4a|max:20480',
    ]);

    if ($v->fails()) {
        \Log::warning('[ArtistController::apply] validation failed', ['errors' => $v->errors()->all()]);
        return response()->json(['success' => false, 'errors' => $v->errors()], 422);
    }

    // Build base data
    $data = [
        'display_name' => $request->input('display_name'),
        'real_name' => $request->input('real_name'),
        'country' => $request->input('country'),
        // leave profile_image as string path if sent as a path; we also accept upload below
        'profile_image' => $request->input('profile_image'),
        'social_links' => $request->input('social_links') ? $request->input('social_links') : null,
    ];

    // meta starts with bio if present
    $meta = array_filter([
        'bio' => $request->input('bio'),
    ]);

    // handle uploaded profile image (avatar)
    if ($request->hasFile('profile_image')) {
        try {
            $pf = $request->file('profile_image');
            $path = $pf->store('artists/avatars', 'public');
            $data['profile_image'] = $path;
        } catch (\Throwable $e) {
            \Log::warning('[ArtistController::apply] profile image store failed: ' . $e->getMessage());
        }
    }

    // handle ID front/back uploads
    if ($request->hasFile('id_front')) {
        try {
            $f = $request->file('id_front');
            $p = $f->store('artists/ids', 'public');
            $meta['id_front'] = $p;
        } catch (\Throwable $e) {
            \Log::warning('[ArtistController::apply] id_front store failed: ' . $e->getMessage());
        }
    }
    if ($request->hasFile('id_back')) {
        try {
            $f = $request->file('id_back');
            $p = $f->store('artists/ids', 'public');
            $meta['id_back'] = $p;
        } catch (\Throwable $e) {
            \Log::warning('[ArtistController::apply] id_back store failed: ' . $e->getMessage());
        }
    }

    if ($request->hasFile('sample_track')) {
        try {
            $track = $request->file('sample_track');

            $path = $track->store(
                'artists/sample_tracks',
                'public'
            );

            $meta['sample_track'] = $path;

        } catch (\Throwable $e) {
            \Log::warning(
                '[ArtistController::apply] sample_track store failed: '
                . $e->getMessage()
            );
        }
    }

    // Merge meta (keep other meta values if updating)
    $data['meta'] = array_merge($meta, $request->input('meta') ? (array)$request->input('meta') : []);

    // Create or update the artist record for this user (unverified by default)
    $artist = Artist::updateOrCreate(
        ['user_id' => $user->id],
        array_merge($data, ['verified' => 0])
    );

    return response()->json([
        'success' => true,
        'data' => $artist
    ], 201);
}



public function me(Request $request)
{
    $user = $request->user();
    $uid = $user ? $user->id : null;
    

    if (!$user) {
        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }

    // find artist record for this user
    $artist = Artist::where('user_id', $user->id)->first();

    if (!$artist) {
        \Log::info('[ArtistController::me] no artist found for user_id: ' . $user->id);
        return response()->json(['status' => 'success', 'data' => ['artist' => null, 'songs' => []]]);
    }

    // Fetch songs explicitly by artist.id to avoid relationship pitfalls
    $songsQuery = Song::where('artist_id', $artist->id)
        ->orderByDesc('created_at');

    $songs = $songsQuery->get()->map(function ($s) {
        return [
            'id' => $s->id,
            'title' => $s->title,
            'status' => $s->status,
            'rejection_reason' => $s->rejection_reason,

            'genre_id' => $s->genre_id,
            'play_count' => (int) ($s->play_count ?? 0),

            'cover_image_url' => $s->cover_image
                ? url('storage/' . ltrim($s->cover_image, '/'))
                : null,

            'file_path' => $s->file_path,
            'audio_url' => url('/api/songs/stream/' . $s->id),
            'created_at' => optional($s->created_at)->toIso8601String(),
        ];
    });

    $artistPayload = [
        'id' => $artist->id,
        'user_id' => $artist->user_id,
        'display_name' => $artist->display_name,
        'profile_image' => $artist->profile_image ? url('storage/' . ltrim($artist->profile_image, '/')) : null,
        'verified' => (bool) $artist->verified,
        'meta' => $artist->meta,
    ];

    \Log::info('[ArtistController::me] artist id='.$artist->id.' songs_count='.$songsQuery->count());

    return response()->json([
        'status' => 'success',
        'data' => [
            'artist' => $artistPayload,
            'songs' => $songs,
        ],
    ]);
}


    public function show(Request $request, Artist $artist)
    {

        $user = $request->user();

        $followersCount = $artist->followers()->count();

        $isOwnProfile = $artist->user_id === $user->id;

        $isFollowing = ArtistFollower::where(
            'user_id',
            $user->id
        )
        ->where(
            'artist_id',
            $artist->id
        )
        ->exists();

        $songs = Song::where('artist_id', $artist->id)
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'title' => $s->title,
                    'cover_image_url' => $s->cover_image
                        ? url('storage/' . ltrim($s->cover_image, '/'))
                        : null,
                    'audio_url' => url('/api/songs/stream/' . $s->id),
                    'play_count' => $s->play_count ?? 0,
                ];
            });

            $followersCount = $artist->followers()->count();

            $alreadyArtist = \App\Models\Artist::where('user_id', $user->id)->exists();

            $hasPendingClaim = false;

            if ($user) {
                $hasPendingClaim = \App\Models\ArtistClaim::where('artist_id',$artist->id)->where('user_id',$user->id)->where('status','pending')->exists();
            }

            return response()->json([
                'success' => true,
                'artist' => [
                'id' => $artist->id,
                'user_id' => $artist->user_id,
                'display_name' => $artist->display_name,
                'real_name' => $artist->real_name,
                'country' => $artist->country,
                'verified' => (bool) $artist->verified,
                'profile_image' => $artist->profile_image
                    ? url('storage/' . ltrim($artist->profile_image, '/'))
                    : null,
                'bio' => $artist->meta['bio'] ?? '',
                'followers_count' => $followersCount,
                'is_following' => $isFollowing,
                'is_own_profile' => $isOwnProfile,
                'can_claim' => (
                    is_null($artist->user_id)
                    && !$alreadyArtist
                    && !$hasPendingClaim
                ),

                'has_pending_claim' => $hasPendingClaim,
                ],
                'songs' => $songs,
            ]);
    }

    public function dashboard()
    {
        $artist = auth()->user()->artist;

        $songs = Song::where('artist_id', $artist->id);

        $followersCount = $artist->followers()->count();

        $topSongs = Song::where('artist_id', $artist->id)
            ->orderByDesc('play_count')
            ->take(5)
            ->get([
                'id',
                'title',
                'play_count',
            ]);

        return response()->json([
            'artist' => [
                'id' => $artist->id,
                'display_name' => $artist->display_name ?: auth()->user()->name,
                'verified' => (bool) $artist->verified,
                'profile_image' => $artist->profile_image
                    ? url('storage/' . ltrim($artist->profile_image, '/'))
                    : null,
            ],

            'total_songs' => $songs->count(),
            'total_plays' => (int) $songs->sum('play_count'),

            'top_song' => Song::where('artist_id', $artist->id)
                ->orderByDesc('play_count')
                ->first(),

            'top_songs' => $topSongs,
            'followers_count' => $followersCount,
        ]);
    }

    public function deleteSong(Song $song)
    {
        $artist = auth()->user()->artist;

        if (!$artist) {
            return response()->json([
                'message' => 'Artist not found'
            ], 404);
        }

        if ($song->artist_id !== $artist->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $song->delete();

        return response()->json([
            'message' => 'Song deleted successfully'
        ]);
    }


    public function updateSong(Request $request, Song $song)
    {

          
        $artist = auth()->user()->artist;



        if (!$artist) {
            return response()->json([
                'message' => 'Artist not found'
            ], 404);
        }

        if ($song->artist_id !== $artist->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'genre_id' => 'required|exists:genres,id',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $song->title = $request->title;
        $song->genre_id = $request->genre_id;

        if ($request->hasFile('cover_image')) {

            if ($song->cover_image) {
                Storage::disk('public')
                    ->delete($song->cover_image);
            }

            $song->cover_image =
                $request->file('cover_image')
                    ->store('covers', 'public');
        }

        $song->save();

        return response()->json([
            'message' => 'Song updated successfully',
            'song' => $song->fresh(),
        ]);
    }

    public function profile(Request $request)
    {
        $artist = Artist::where(
            'user_id',
            $request->user()->id
        )->firstOrFail();

        return response()->json([
            'display_name' => $artist->display_name,
            'country' => $artist->country,
            'bio' => $artist->meta['bio'] ?? '',
            'profile_image' => $artist->profile_image
                ? url('storage/' . $artist->profile_image)
                : null,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $artist = Artist::where(
            'user_id',
            $request->user()->id
        )->firstOrFail();

        $request->validate([
            'display_name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'profile_image' => 'nullable|image|max:5120',
        ]);

        $artist->display_name =
            $request->display_name;

        $artist->country =
            $request->country;

        $meta = $artist->meta ?? [];

        $meta['bio'] =
            $request->bio;

        $artist->meta = $meta;

        if ($request->hasFile('profile_image')) {

            if ($artist->profile_image) {
                \Storage::disk('public')
                    ->delete(
                        $artist->profile_image
                    );
            }

            $artist->profile_image =
                $request->file(
                    'profile_image'
                )->store(
                    'artists/avatars',
                    'public'
                );
        }

        $artist->save();

        return response()->json([
            'message' =>
                'Artist profile updated',
        ]);
    }

    public function popular()
    {
        $artists = Artist::withCount('followers')
            ->where('verified', true)
            ->orderByDesc('followers_count')
            ->take(10)
            ->get()
            ->map(function ($artist) {
                return [
                    'id' => $artist->id,
                    'display_name' => $artist->display_name,
                    'verified' => (bool) $artist->verified,
                    'followers_count' => $artist->followers_count,
                    'profile_image' => $artist->profile_image
                        ? url('storage/' . ltrim($artist->profile_image, '/'))
                        : null,
                ];
            });

        return response()->json([
            'artists' => $artists,
        ]);
    }


    public function recentlyVerified()
    {
        $artists = Artist::withCount('followers')
            ->where('verified', true)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($artist) {
                return [
                    'id' => $artist->id,
                    'display_name' => $artist->display_name,
                    'verified' => (bool) $artist->verified,
                    'followers_count' => $artist->followers_count,
                    'profile_image' => $artist->profile_image
                        ? url('storage/' . ltrim($artist->profile_image, '/'))
                        : null,
                ];
            });

        return response()->json([
            'artists' => $artists,
        ]);
    }


    public function artistStatus(Request $request)
    {
        $user = $request->user();

        $artist = Artist::where(
            'user_id',
            $user->id
        )->first();

        if ($artist) {
            return response()->json([
                'is_artist' => true,
                'artist_id' => $artist->id,
                'has_pending_claim' => false,
                'possible_artist_match' => false,
            ]);
        }

        $pendingClaim = \App\Models\ArtistClaim::where(
            'user_id',
            $user->id
        )
        ->where(
            'status',
            'pending'
        )
        ->first();

        if ($pendingClaim) {
            return response()->json([
                'is_artist' => false,
                'has_pending_claim' => true,
                'possible_artist_match' => false,
            ]);
        }

        $possibleArtist = Artist::query()
            ->whereNull('user_id')
            ->where(function ($q) use ($user) {

                $name = strtolower(trim($user->name));

                $q->whereRaw(
                    'LOWER(display_name) = ?',
                    [$name]
                )
                ->orWhereRaw(
                    'LOWER(real_name) = ?',
                    [$name]
                );
            })
            ->first();

            if ($possibleArtist) {
                return response()->json([
                    'is_artist' => false,
                    'has_pending_claim' => false,
                    'possible_artist_match' => true,

                    'matched_artist_id' => $possibleArtist->id,
                    'matched_artist_name' => $possibleArtist->display_name,

                    'matched_artist_image' => $possibleArtist->profile_image
                        ? url('storage/' . ltrim($possibleArtist->profile_image, '/'))
                        : null,

                    'matched_artist_country' => $possibleArtist->country,

                    'matched_artist_verified' => (bool) $possibleArtist->verified,
                ]);
            }
        return response()->json([
            'is_artist' => false,
            'has_pending_claim' => false,
            'possible_artist_match' => false,
        ]);
    }


}
