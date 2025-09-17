<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Song;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SongApiController extends Controller
{
    /* --------------------------------- Helpers -------------------------------- */

    protected function isAdmin($user): bool
    {
        if (!$user) return false;
        // support either a boolean or a role column
        return ($user->is_admin ?? false) || (($user->role ?? null) === 'admin');
    }

    protected function canSeeUnpublished($user, Song $song): bool
    {
        if (!$user) return false;
        if ($this->isAdmin($user)) return true;

        // If your Song owns an artist_id that references artists.id,
        // replace with: $song->artist_id === ($user->artist->id ?? null)
        return ($song->artist_user_id ?? null) === $user->id   // if you store uploader user id
            || ($song->artist_id ?? null) === ($user->artist->id ?? null);
    }

    /**
     * Build song payload (absolute URLs, created_at ISO, like flag).
     */
    protected function transformSong(Request $request, Song $song, array $likedIds = [])
    {
        $baseUrl = $request->getSchemeAndHttpHost();

        return [
            'id'              => $song->id,
            'title'           => $song->title,
            'artist'          => $song->artist,
            'genre_id'        => $song->genre_id,
            'genre'           => optional($song->genre)->name,
            'audio_url'       => $baseUrl . '/api/songs/stream/' . $song->id,
            'cover_image_url' => $song->cover_image ? $baseUrl . '/storage/' . ltrim($song->cover_image, '/') : null,
            'created_at'      => optional($song->created_at)->toIso8601String(),
            'is_liked'        => in_array($song->id, $likedIds),
            'status'          => $song->status, // useful on "my uploads"
        ];
    }

    /**
     * Apply visibility filter:
     * - guests: published only
     * - logged in: published OR their own uploads
     * - admins: everything
     */
    protected function visibilityQuery($query, $user)
    {
        if ($this->isAdmin($user)) {
            return $query; // no filter
        }

        if ($user) {
            return $query->where(function ($q) use ($user) {
                $q->where('status', 'published')
                  ->orWhere('artist_user_id', $user->id) // if you store uploader user id
                  ->orWhere('artist_id', optional($user->artist)->id); // if you store artists.id
            });
        }

        return $query->where('status', 'published');
    }

    /* -------------------------------- Endpoints ------------------------------- */

    /** GET /api/songs */
    public function index(Request $request)
    {
        $user = $request->user();

        $songs = $this->visibilityQuery(Song::query(), $user)
            ->latest()
            ->take(25)
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s));

        return response()->json(['status' => 'success', 'data' => $songs]);
    }

    /** Stream with Range support; guard unpublished */
    public function stream(Request $request, $id)
    {
        $song = Song::findOrFail($id);
        $user = $request->user();

        // Block streaming for unpublished unless admin or owner
        if ($song->status !== 'published' && !$this->canSeeUnpublished($user, $song)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // Increment plays non-fatally
        try { $song->increment('play_count'); } catch (\Throwable $e) {
            Log::warning("[SongApiController::stream] increment failed: {$song->id} :: ".$e->getMessage());
        }

        if ($request->query('debug') === '1') {
            return response()->json([
                'id' => $song->id,
                'file_path' => $song->file_path,
                'audio_url' => $song->audio_url,
                'status' => $song->status,
            ]);
        }

        $disk = Storage::disk('public');

        $isAbsoluteUrl = function (?string $url): bool {
            return $url && (bool) preg_match('#^https?://#i', $url);
        };

        $mapAudioUrlToStoragePath = function (string $url) use ($request) : ?string {
            $parsed = parse_url($url);
            if (!isset($parsed['path'])) return null;
            $path = $parsed['path'];

            if (str_starts_with($path, '/storage/')) {
                return ltrim(substr($path, strlen('/storage/')), '/');
            }

            $host = $request->getHost();
            if (isset($parsed['host']) && ($parsed['host'] === $host || str_contains($parsed['host'], $host))) {
                if (str_contains($path, '/storage/')) {
                    $idx = strpos($path, '/storage/');
                    return ltrim(substr($path, $idx + strlen('/storage/')), '/');
                }
            }
            return null;
        };

        if (!empty($song->audio_url) && $isAbsoluteUrl($song->audio_url)) {
            if ($maybeRel = $mapAudioUrlToStoragePath($song->audio_url)) {
                $song->file_path = ltrim($maybeRel, '/');
            } else {
                Log::info("[SongApiController::stream] redirect external audio_url #{$song->id}");
                return redirect()->away($song->audio_url);
            }
        }

        $filePath = $song->file_path ? ltrim($song->file_path, '/') : null;
        if (!$filePath || !$disk->exists($filePath)) {
            Log::error("[SongApiController::stream] file missing #{$song->id}: {$filePath}");
            return response()->json(['status' => 'error', 'message' => 'Audio file missing on server'], 404);
        }

        $fullPath = $disk->path($filePath);
        $size = @filesize($fullPath);
        if ($size === false || $size <= 0) {
            Log::error("[SongApiController::stream] invalid size #{$song->id}: {$fullPath}");
            return response()->json(['status' => 'error', 'message' => 'Invalid audio file'], 500);
        }

        $mime = @mime_content_type($fullPath) ?: 'audio/mpeg';
        $rangeHeader = $request->header('Range');

        $start = 0; $end = $size - 1; $status = 200;
        $headers = ['Content-Type'=>$mime,'Accept-Ranges'=>'bytes','Cache-Control'=>'public, max-age=31536000'];

        if ($rangeHeader && preg_match('/bytes=(\d*)-(\d*)/', $rangeHeader, $m)) {
            $status = 206;
            if ($m[1] !== '') $start = intval($m[1]);
            if ($m[2] !== '') $end = intval($m[2]);
            if ($start > $end || $start >= $size) {
                return response('', 416)->header('Content-Range', "bytes */{$size}");
            }
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
            $headers['Content-Length'] = (string) (($end - $start) + 1);
        } else {
            $headers['Content-Length'] = (string) $size;
        }

        $response = new StreamedResponse(function () use ($fullPath, $start, $end, $song) {
            $h = fopen($fullPath, 'rb');
            if ($h === false) {
                Log::error("[SongApiController::stream] fopen failed #{$song->id}");
                return;
            }
            try {
                fseek($h, $start);
                $buf = 1024 * 64;
                $left = ($end - $start) + 1;
                while ($left > 0 && !feof($h)) {
                    $read = $left > $buf ? $buf : $left;
                    $data = fread($h, $read);
                    if ($data === false) break;
                    echo $data;
                    flush();
                    $left -= strlen($data);
                }
            } finally {
                fclose($h);
            }
        }, $status, $headers);

        return $response;
    }

    /** GET /api/songs/most-played */
    public function mostPlayed(Request $request)
    {
        $user = $request->user();

        $songs = $this->visibilityQuery(Song::orderByDesc('play_count'), $user)
            ->take(12)
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s));

        return response()->json(['status' => 'success', 'data' => $songs]);
    }

    public function markPlayed(Song $song)
    {
        $song->increment('play_count');
        return response()->json(['status' => 'ok', 'play_count' => $song->play_count]);
    }

    public function incrementPlayCount($id)
    {
        $song = Song::findOrFail($id);
        $song->increment('play_count');

        return response()->json([
            'status'  => 'success',
            'message' => 'Play count updated',
            'data'    => $song,
        ]);
    }

    /** GET /api/songs/search?q= */
    public function search(Request $request)
    {
        $query = (string) $request->input('q', '');
        $user  = $request->user();

        $likedIds = $user
            ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        $songs = $this->visibilityQuery(
                Song::where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('artist', 'like', "%{$query}%");
                }),
                $user
            )
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json(['status' => 'success', 'data' => $songs]);
    }

    /** GET /api/songs/{id} */
    public function show(Request $request, $id)
    {
        $song = Song::findOrFail($id);
        $user = $request->user();

        if ($song->status !== 'published' && !$this->canSeeUnpublished($user, $song)) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        // keep previous non-fatal increment
        try { $song->increment('play_count'); } catch (\Throwable $e) {
            Log::warning('[Show] play_count increment failed: '.$e->getMessage());
        }

        $likedIds = $user
            ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        return response()->json([
            'status' => 'success',
            'data'   => $this->transformSong($request, $song, $likedIds),
        ]);
    }

    /** POST /api/songs  (mobile upload) */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'artist'      => 'nullable|string|max:255',
            'file'        => 'required|file|mimes:mp3,wav,m4a,ogg,flac|max:51200',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'album_id'    => 'nullable|exists:albums,id',
            'genre_id'    => 'required|exists:genres,id',
        ]);

        $user = $request->user();

        $coverPath = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('covers', 'public')
            : null;

        $filePath = $request->file('file')->store('songs', 'public');

        // status logic: admin uploads go live, others pending
        $status = $this->isAdmin($user) ? 'published' : 'pending';

        $song = Song::create([
            'title'           => $request->title,
            'artist'          => $request->artist,
            'file_path'       => $filePath,
            'cover_image'     => $coverPath,
            'album_id'        => $request->album_id,
            'genre_id'        => $request->genre_id,
            'status'          => $status,
            // Store who uploaded (optional; uncomment if your schema has these):
            // 'artist_user_id'   => $user?->id,
            // 'artist_id'        => optional($user->artist)->id,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Song uploaded successfully',
            'data'    => $this->transformSong($request, $song),
        ], 201);
    }

    /** PUT /api/songs/{id} */
    public function update(Request $request, $id)
    {
        $song = Song::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'artist'      => 'required|string|max:255',
            'genre_id'    => 'required|exists:genres,id',
            'file'        => 'nullable|mimes:mp3,wav,ogg,m4a,flac|max:10000',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'album_id'    => 'nullable|exists:albums,id',
        ]);

        if ($request->hasFile('file')) {
            if ($song->file_path) Storage::disk('public')->delete($song->file_path);
            $song->file_path = $request->file('file')->store('songs', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($song->cover_image) Storage::disk('public')->delete($song->cover_image);
            $song->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        $song->title    = $request->title;
        $song->artist   = $request->artist;
        $song->genre_id = $request->genre_id;
        $song->album_id = $request->album_id;
        $song->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Song updated successfully',
            'data'    => $this->transformSong($request, $song),
        ]);
    }

    /** DELETE /api/songs/{id} */
    public function destroy($id)
    {
        $song = Song::findOrFail($id);

        if ($song->file_path) Storage::disk('public')->delete($song->file_path);
        if ($song->cover_image) Storage::disk('public')->delete($song->cover_image);

        $song->delete();

        return response()->json(['status' => 'success', 'message' => 'Song deleted successfully']);
    }

    /** GET /api/songs/recent */
    public function recent(Request $request)
    {
        $user = $request->user();
        $likedIds = $user
            ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        $songs = $this->visibilityQuery(Song::query(), $user)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json(['status' => 'success', 'data' => $songs]);
    }

    /** GET /api/songs/genre/{id} */
    public function byGenre(Request $request, $id)
    {
        $user = $request->user();
        $likedIds = $user
            ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        $songs = $this->visibilityQuery(Song::where('genre_id', $id), $user)
            ->latest()
            ->take(25)
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json(['status' => 'success', 'data' => $songs]);
    }

    /** GET /api/songs/by-genre-id/{id} (alias) */
    public function byGenreId(Request $request, $id)
    {
        return $this->byGenre($request, $id);
    }

    /** GET /api/songs/genres */
    public function genres()
    {
        $genres = Song::select('genre')
            ->whereNotNull('genre')
            ->distinct()
            ->pluck('genre');

        return response()->json(['status' => 'success', 'data' => $genres]);
    }

    /** POST /api/songs/{song}/like */
    public function likeSong(Request $request, Song $song)
    {
        $user = $request->user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        DB::table('liked_songs')->updateOrInsert(
            ['user_id' => $user->id, 'song_id' => $song->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return response()->json(['status' => 'success', 'liked' => true, 'song_id' => $song->id]);
    }

    /** DELETE /api/songs/{song}/like */
    public function unlikeSong(Request $request, Song $song)
    {
        $user = $request->user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        DB::table('liked_songs')->where('user_id', $user->id)->where('song_id', $song->id)->delete();

        return response()->json(['status' => 'success', 'liked' => false, 'song_id' => $song->id]);
    }

    /** GET /api/me/likes */
    public function likedSongs(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        $likedIds = DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray();

        $songs = Song::query()
            ->join('liked_songs', 'songs.id', '=', 'liked_songs.song_id')
            ->where('liked_songs.user_id', $user->id)
            ->when(!$this->isAdmin($user), function ($q) {
                $q->where('songs.status', 'published');
            })
            ->orderByDesc('liked_songs.created_at')
            ->select('songs.*')
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json(['status' => 'success', 'songs' => $songs]);
    }

    /** GET /api/songs/most-liked */
    public function mostLiked(Request $request)
    {
        $user = $request->user();

        $base = Song::select('songs.*')
            ->leftJoin('liked_songs', 'songs.id', '=', 'liked_songs.song_id')
            ->selectRaw('COUNT(liked_songs.id) as likes_count')
            ->groupBy('songs.id');

        $songs = $this->visibilityQuery($base, $user)
            ->orderByDesc('likes_count')
            ->take(15)
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s));

        return response()->json(['status' => 'success', 'data' => $songs]);
    }

    /** Premium download */
    public function download(Request $request, Song $song)
    {
        $user = $request->user();
        if (!$user || !$user->is_premium) {
            return response()->json(['message' => 'Premium required'], 403);
        }

        // Also enforce published visibility for downloads (unless admin/owner)
        if ($song->status !== 'published' && !$this->canSeeUnpublished($user, $song)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $filePath = storage_path('app/public/' . $song->file_path);
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->download(
            $filePath,
            basename($filePath),
            [
                'Content-Type'        => mime_content_type($filePath) ?: 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="'.basename($filePath).'"'
            ]
        );
    }
}
