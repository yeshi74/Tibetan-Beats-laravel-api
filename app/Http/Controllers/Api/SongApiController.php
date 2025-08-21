<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Song;

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
            'genre'           => $song->genre,
            'artist'          => $song->artist,
            'audio_url'       => $baseUrl . '/api/songs/stream/' . $song->id,
            'cover_image_url' => $song->cover_image ? $baseUrl . '/storage/' . $song->cover_image : null,
            'created_at'      => optional($song->created_at)->toIso8601String(),
            'is_liked'        => in_array($song->id, $likedIds),
        ];
    }

    /**
     * GET /api/songs
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $likedIds = $user
            ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        $songs = Song::orderBy('created_at', 'desc')->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json([
            'status' => 'success',
            'data'   => $songs,
        ]);
    }

    /**
     * Audio streaming with range support
     * GET /api/songs/stream/{id}
     */
    public function stream($id)
    {
        $song = Song::findOrFail($id);
        $filePath = storage_path('app/public/' . $song->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        $size   = filesize($filePath);
        $length = $size;
        $start  = 0;
        $end    = $size - 1;

        header('Content-Type: audio/mpeg');
        header('Accept-Ranges: bytes');

        if (isset($_SERVER['HTTP_RANGE'])) {
            [$param, $range] = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strtolower(trim($param)) !== 'bytes') {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                exit;
            }

            [$start, $end] = explode('-', $range, 2);
            $start = intval($start);

            $end = ($end === '') ? ($size - 1) : intval($end);
            $length = ($end - $start) + 1;

            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$size");
        }

        header('Content-Length: ' . $length);

        $fp = fopen($filePath, 'rb');
        fseek($fp, $start);
        $bufferSize = 1024 * 8;
        while (!feof($fp) && ($pos = ftell($fp)) <= $end) {
            if ($pos + $bufferSize > $end) {
                $bufferSize = $end - $pos + 1;
            }
            echo fread($fp, $bufferSize);
            flush();
        }
        fclose($fp);
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
            'file'        => 'required|file|mimes:mp3,wav|max:51200',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'album_id'    => 'nullable|exists:albums,id',
            'genre'       => 'required|string|max:100',
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
            'genre'       => $request->genre,
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
            'genre'       => 'required|string|max:100',
            'file'        => 'nullable|mimes:mp3,wav,ogg|max:10000',
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
        $song->genre    = $request->genre;
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
    public function byGenre(Request $request, $genre)
    {
        $user = $request->user();
        $likedIds = $user
            ? DB::table('liked_songs')->where('user_id', $user->id)->pluck('song_id')->toArray()
            : [];

        $songs = Song::where('genre', $genre)->latest()->take(15)->get()
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

        // Get liked song IDs for this user
        $likedIds = DB::table('liked_songs')
            ->where('user_id', $user->id)
            ->pluck('song_id')
            ->toArray();

        // Pull the songs, ordered by when they were liked
        $songs = Song::query()
            ->join('liked_songs', 'songs.id', '=', 'liked_songs.song_id')
            ->where('liked_songs.user_id', $user->id)
            ->orderByDesc('liked_songs.created_at')
            ->select('songs.*') // ← avoid ambiguous columns
            ->get()
            ->map(fn ($s) => $this->transformSong($request, $s, $likedIds));

        return response()->json([
            'status' => 'success',
            'songs'  => $songs,
        ]);
    }

}
