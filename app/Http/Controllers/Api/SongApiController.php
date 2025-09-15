<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Song;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SongApiController extends Controller
{
    /**
     * Helper: build a song array with absolute urls, created_at, and is_liked
     */
    protected function transformSong(Request $request, Song $song, array $likedIds = [])
    {
        $baseUrl = $request->getSchemeAndHttpHost();

        return [
            'id'              => $song->id,
            'title'           => $song->title,
            'artist'          => $song->artist,
            'genre_id'        => $song->genre_id,
            'genre'           => $song->genre ? $song->genre->name : null,
            'audio_url'       => $baseUrl . '/api/songs/stream/' . $song->id,
            'cover_image_url' => $song->cover_image ? $baseUrl . '/storage/' . ltrim($song->cover_image, '/') : null,
            'created_at'      => optional($song->created_at)->toIso8601String(),
            'is_liked'        => in_array($song->id, $likedIds),
        ];
    }

    /**
     * GET /api/songs
     */
    public function index(Request $request)
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $user = $request->user(); // null if not authenticated

        // Only fetch published songs
        $songs = Song::where('status', 'published')
            ->latest()
            ->get()
            ->map(function ($song) use ($baseUrl, $user) {
                $data = [
                    'id'              => $song->id,
                    'title'           => $song->title,
                    'genre'           => $song->genre,
                    'artist'          => $song->artist,
                    'audio_url'       => $baseUrl . '/api/songs/stream/' . $song->id,
                    'cover_image_url' => $song->cover_image
                        ? $baseUrl . '/storage/' . ltrim($song->cover_image, '/')
                        : null,
                    'created_at'      => optional($song->created_at)->toIso8601String(),
                ];

                // Only premium users get direct download
                if ($user && $user->is_premium) {
                    $data['download_url'] = $baseUrl . '/api/songs/' . $song->id . '/download';
                }

                return $data;
            });

        return response()->json([
            'status' => 'success',
            'data'   => $songs,
        ]);
    }

    /**
     * Audio streaming with range support
     * GET /api/songs/stream/{id}
     *
     * This implementation uses StreamedResponse and supports HTTP Range requests
     * required by many mobile audio players (iOS AVFoundation).
     */
    public function stream(Request $request, $id)
{
    $song = Song::findOrFail($id);

    // non-fatal increment of play_count
    try {
        $song->increment('play_count');
    } catch (\Throwable $e) {
        Log::warning("[SongApiController::stream] increment failed for song {$id}: " . $e->getMessage());
    }

    // debug: quick metadata
    if ($request->query('debug') === '1') {
        return response()->json([
            'id' => $song->id,
            'file_path' => $song->file_path,
            'audio_url' => $song->audio_url,
        ]);
    }

    $disk = Storage::disk('public');

    // Helper: check if URL is absolute
    $isAbsoluteUrl = function (?string $url): bool {
        if (!$url) return false;
        return (bool) preg_match('#^https?://#i', $url);
    };

    // Helper: if audio_url is same-host and points to /storage/ we'll map it to storage path
    $mapAudioUrlToStoragePath = function (string $url) use ($request) : ?string {
        // Normalize
        $parsed = parse_url($url);
        if (!isset($parsed['path'])) return null;
        $path = $parsed['path'];

        // If path begins with /storage/ -> the storage relative path is substring after /storage/
        if (str_starts_with($path, '/storage/')) {
            return ltrim(substr($path, strlen('/storage/')), '/');
        }

        // If host matches app host (or host contains it) and path has /storage/ anywhere, use substring
        $host = $request->getHost();
        if (isset($parsed['host']) && (str_contains($parsed['host'], $host) || $parsed['host'] === $host)) {
            if (str_contains($path, '/storage/')) {
                $idx = strpos($path, '/storage/');
                return ltrim(substr($path, $idx + strlen('/storage/')), '/');
            }
        }

        return null;
    };

    // If audio_url is present and absolute, try to handle mapping to storage or proxy/redirect
    if (!empty($song->audio_url) && $isAbsoluteUrl($song->audio_url)) {
        // If it maps to a local storage path (e.g. http://host/storage/...), stream from disk directly
        $maybeRel = $mapAudioUrlToStoragePath($song->audio_url);
        if ($maybeRel !== null) {
            // treat as local file
            $filePath = ltrim($maybeRel, '/');
            if (!$disk->exists($filePath)) {
                Log::error("[SongApiController::stream] mapped storage file missing for song {$id}: {$filePath}");
                return response()->json(['status' => 'error', 'message' => 'Audio file missing on server'], 404);
            }
            // replace $song->file_path with mapped path to reuse below streaming logic
            $song->file_path = $filePath;
            // fall through to local-file streaming
        } else {
            // external host. Prefer redirect when external, but if some clients fail you can implement proxying.
            Log::info("[SongApiController::stream] redirecting to external audio_url for song {$id}");
            return redirect()->away($song->audio_url);
        }
    }

    // At this point, stream from local storage using file_path
    $filePath = $song->file_path ?? null;
    if (!$filePath) {
        Log::error("[SongApiController::stream] song {$id} has no file_path or streamable audio_url");
        return response()->json(['status' => 'error', 'message' => 'No audio file associated'], 404);
    }

    $filePath = ltrim($filePath, '/');
    if (!$disk->exists($filePath)) {
        Log::error("[SongApiController::stream] file missing for song {$id}: {$filePath}");
        return response()->json(['status' => 'error', 'message' => 'Audio file missing on server'], 404);
    }

    $fullPath = $disk->path($filePath);
    $size = @filesize($fullPath);
    if ($size === false || $size <= 0) {
        Log::error("[SongApiController::stream] invalid file size for song {$id}: {$fullPath}");
        return response()->json(['status' => 'error', 'message' => 'Invalid audio file'], 500);
    }

    $mime = @mime_content_type($fullPath) ?: 'audio/mpeg';
    $rangeHeader = $request->header('Range');

    $start = 0;
    $end = $size - 1;
    $status = 200;
    $headers = [
        'Content-Type' => $mime,
        'Accept-Ranges' => 'bytes',
        'Cache-Control' => 'public, max-age=31536000',
    ];

    if ($rangeHeader && preg_match('/bytes=(\d*)-(\d*)/', $rangeHeader, $matches)) {
        $status = 206;
        if ($matches[1] !== '') $start = intval($matches[1]);
        if ($matches[2] !== '') $end = intval($matches[2]);
        if ($start > $end || $start >= $size) {
            Log::warning("[SongApiController::stream] invalid range for song {$id}: {$rangeHeader}");
            return response('', 416)->header('Content-Range', "bytes */{$size}");
        }
        $contentLength = ($end - $start) + 1;
        $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
    } else {
        $contentLength = $size;
    }

    $headers['Content-Length'] = (string) $contentLength;

    $response = new StreamedResponse(function () use ($fullPath, $start, $end, $id) {
        $handle = fopen($fullPath, 'rb');
        if ($handle === false) {
            Log::error("[SongApiController::stream] fopen failed for song {$id}");
            return;
        }
        try {
            fseek($handle, $start);
            $buffer = 1024 * 64;
            $bytesLeft = ($end - $start) + 1;
            while ($bytesLeft > 0 && !feof($handle)) {
                $read = ($bytesLeft > $buffer) ? $buffer : $bytesLeft;
                $data = fread($handle, $read);
                if ($data === false) break;
                echo $data;
                flush();
                $bytesLeft -= strlen($data);
            }
        } finally {
            fclose($handle);
        }
    }, $status, $headers);

    return $response;
}

    public function mostPlayed()
    {
        $songs = \App\Models\Song::orderByDesc('play_count')
            ->take(12)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $songs,
        ]);
    }

    public function markPlayed(\App\Models\Song $song)
    {
        // Lightweight increment; no auth needed
        $song->increment('play_count');
        return response()->json(['status' => 'ok', 'play_count' => $song->play_count]);
    }

    public function incrementPlayCount($id)
    {
        $song = Song::findOrFail($id);
        $song->increment('play_count');

        return response()->json([
            'status' => 'success',
            'message' => 'Play count updated',
            'data'   => $song,
        ]);
    }

    /**
     * GET /api/songs/search?q=
     */
    public function search(Request $request)
    {
        $query = (string) $request->input('q', '');
        $user = $request->user();
        $likedIds = $user
            ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        $songs = Song::where('title', 'like', "%{$query}%")
            ->orWhere('artist', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json([
            'status' => 'success',
            'data'   => $songs
        ]);
    }

    /**
     * GET /api/songs/{id}
     */
    public function show(Request $request, $id)
    {
        $song = Song::findOrFail($id);
        $user = $request->user();
        $likedIds = $user
            ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        // maintain previous behavior (increment)
        try {
            $song->increment('play_count');
        } catch (\Throwable $e) {
            \Log::warning('[Show] play_count increment failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'data'   => $this->transformSong($request, $song, $likedIds),
        ]);
    }

    /**
     * POST /api/songs
     */
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

        $coverPath = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('covers', 'public')
            : null;

        $filePath = $request->file('file')->store('songs', 'public');

        $song = Song::create([
            'title'       => $request->title,
            'artist'      => $request->artist,
            'file_path'   => $filePath,
            'cover_image' => $coverPath,
            'album_id'    => $request->album_id,
            'genre_id'    => $request->genre_id,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Song uploaded successfully',
            'data'    => $this->transformSong($request, $song),
        ], 201);
    }

    /**
     * PUT /api/songs/{id}
     */
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
            if ($song->file_path) {
                Storage::disk('public')->delete($song->file_path);
            }
            $song->file_path = $request->file('file')->store('songs', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($song->cover_image) {
                Storage::disk('public')->delete($song->cover_image);
            }
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

    /**
     * DELETE /api/songs/{id}
     */
    public function destroy($id)
    {
        $song = Song::findOrFail($id);

        if ($song->file_path) {
            Storage::disk('public')->delete($song->file_path);
        }
        if ($song->cover_image) {
            Storage::disk('public')->delete($song->cover_image);
        }

        $song->delete();

        return response()->json(['status' => 'success', 'message' => 'Song deleted successfully']);
    }

    /**
     * GET /api/songs/recent
     */
    public function recent(Request $request)
    {
        $user = $request->user();
        $likedIds = $user
            ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        $songs = Song::orderBy('created_at', 'desc')->take(15)->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json([
            'status' => 'success',
            'data'   => $songs,
        ]);
    }

    /**
     * GET /api/songs/genre/{genre}
     */
    public function byGenre(Request $request, $id)
    {
        $user = $request->user();
        $likedIds = $user
            ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        $songs = Song::where('genre_id', $id)
            ->latest()
            ->take(25)
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json([
            'status' => 'success',
            'data'   => $songs,
        ]);
    }

    public function byGenreId(Request $request, $id)
    {
        $user = $request->user();
        $likedIds = $user
            ? \DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        $songs = \App\Models\Song::where('genre_id', $id)->latest()->take(25)->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json([
            'status' => 'success',
            'data'   => $songs,
        ]);
    }

    /**
     * GET /api/songs/genres
     */
    public function genres()
    {
        $genres = Song::select('genre')
            ->whereNotNull('genre')
            ->distinct()
            ->pluck('genre');

        return response()->json([
            'status' => 'success',
            'data'   => $genres
        ]);
    }

    /**
     * POST /api/songs/{song}/like
     */
    public function likeSong(Request $request, Song $song)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        DB::table('liked_songs')->updateOrInsert(
            ['user_id' => $user->id, 'song_id' => $song->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return response()->json([
            'status'  => 'success',
            'liked'   => true,
            'song_id' => $song->id,
        ]);
    }

    /**
     * DELETE /api/songs/{song}/like
     */
    public function unlikeSong(Request $request, Song $song)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        DB::table('liked_songs')
            ->where('user_id', $user->id)
            ->where('song_id', $song->id)
            ->delete();

        return response()->json([
            'status'  => 'success',
            'liked'   => false,
            'song_id' => $song->id,
        ]);
    }

    /**
     * GET /api/me/likes
     */
    public function likedSongs(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $likedIds = DB::table('liked_songs')
            ->where('user_id', $user->id)
            ->pluck('song_id')
            ->toArray();

        $songs = Song::query()
            ->join('liked_songs', 'songs.id', '=', 'liked_songs.song_id')
            ->where('liked_songs.user_id', $user->id)
            ->orderByDesc('liked_songs.created_at')
            ->select('songs.*')
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json([
            'status' => 'success',
            'songs'  => $songs,
        ]);
    }

    public function mostLiked(Request $request)
    {
        $user = $request->user();
        $likedIds = $user
            ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        $songs = Song::select('songs.*')
            ->leftJoin('liked_songs', 'songs.id', '=', 'liked_songs.song_id')
            ->selectRaw('COUNT(liked_songs.id) as likes_count')
            ->groupBy('songs.id')
            ->orderByDesc('likes_count')
            ->take(15)
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json([
            'status' => 'success',
            'data'   => $songs,
        ]);
    }

    public function download(Request $request, Song $song)
    {
        $user = $request->user();
        if (!$user || !$user->is_premium) {
            return response()->json(['message' => 'Premium required'], 403);
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
