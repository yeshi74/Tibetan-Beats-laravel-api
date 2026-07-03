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
use App\Models\ArtistApplication;
use App\Models\ArtistClaim;
use App\Services\SongTransformer;

class ArtistController extends Controller
{
    // app/Http/Controllers/Api/ArtistController.php (method apply)
public function apply(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    // Already an approved artist?
    $existingArtist = Artist::where(
        'user_id',
        $user->id
    )->first();

    if ($existingArtist) {
        return response()->json([
            'success' => false,
            'message' => 'You are already an artist.'
        ], 422);
    }

    // Existing pending application?
    $pendingApplication = ArtistApplication::where(
        'user_id',
        $user->id
    )
    ->where('status', 'pending')
    ->first();

    if ($pendingApplication) {
        return response()->json([
            'success' => false,
            'message' => 'Your application is already under review.'
        ], 422);
    }

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
        return response()->json([
            'success' => false,
            'errors' => $v->errors()
        ], 422);
    }

    $data = [
        'display_name' => $request->input('display_name'),
        'real_name' => $request->input('real_name'),
        'country' => $request->input('country'),
        'profile_image' => $request->input('profile_image'),
        'social_links' => $request->input('social_links'),
    ];

    $meta = array_filter([
        'bio' => $request->input('bio'),
    ]);

    if ($request->hasFile('profile_image')) {
        $data['profile_image'] = $request
            ->file('profile_image')
            ->store('artists/avatars', 'public');
    }

    if ($request->hasFile('id_front')) {
        $meta['id_front'] = $request
            ->file('id_front')
            ->store('artists/ids', 'public');
    }

    if ($request->hasFile('id_back')) {
        $meta['id_back'] = $request
            ->file('id_back')
            ->store('artists/ids', 'public');
    }

    if ($request->hasFile('sample_track')) {
        $meta['sample_track'] = $request
            ->file('sample_track')
            ->store('artists/sample_tracks', 'public');
    }

    $data['meta'] = array_merge(
        $meta,
        $request->input('meta')
            ? (array) $request->input('meta')
            : []
    );

    $application = ArtistApplication::create([
        'user_id' => $user->id,
        'display_name' => $data['display_name'],
        'real_name' => $data['real_name'],
        'country' => $data['country'],
        'profile_image' => $data['profile_image'],
        'social_links' => $data['social_links'],
        'meta' => $data['meta'],
        'status' => 'pending',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Artist application submitted successfully.',
        'data' => $application,
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
        if (!$artist->show_profile) {
            return response()->json([
                'message' => 'Profile not available'
            ], 404);
        }

        $user = $this->resolveUser($request);

        $followersCount = $artist->followers()->count();

        $isOwnProfile = $user
            ? $artist->user_id === $user->id
            : false;

        $isFollowing = $user
            ? ArtistFollower::where('user_id', $user->id)
                ->where('artist_id', $artist->id)
                ->exists()
            : false;

        $songs = Song::visible()->where('artist_id', $artist->id)
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

        $alreadyArtist = false;
        $hasPendingClaim = false;

        if ($user) {
            $alreadyArtist = Artist::where('user_id', $user->id)->exists();

            $hasPendingClaim = \App\Models\ArtistClaim::where('artist_id', $artist->id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->exists();
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

    protected function resolveUser(Request $request)
    {
        $user = $request->user();
        if ($user) {
            return $user;
        }

        try {
            return auth('sanctum')->user();
        } catch (\Throwable $e) {
            return null;
        }
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
            ->where('show_profile', true)
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
            ->where('show_profile', true)
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

        // Approved artist
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

        // Pending artist application
        $pendingApplication = ArtistApplication::where(
            'user_id',
            $user->id
        )
        ->where('status', 'pending')
        ->first();

        if ($pendingApplication) {
            return response()->json([
                'is_artist' => false,
                'has_pending_claim' => true,
                'possible_artist_match' => false,
            ]);
        }

        // Pending ownership claim
        $pendingClaim = ArtistClaim::where(
            'user_id',
            $user->id
        )
        ->where('status', 'pending')
        ->first();

        if ($pendingClaim) {
            return response()->json([
                'is_artist' => false,
                'has_pending_claim' => true,
                'possible_artist_match' => false,
            ]);
        }

        // Existing unclaimed artist profile match
        $possibleArtist = Artist::where('show_profile', true)
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
