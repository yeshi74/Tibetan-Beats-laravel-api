<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Song;

class SongApiController extends Controller
{
    public function index(Request $request)
    {
        $baseUrl = $request->getSchemeAndHttpHost();

        $songs = Song::all()->map(function ($song) use ($baseUrl) {
            return [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist,
                'audio_url' => $baseUrl . '/api/songs/stream/' . $song->id,
                'cover_image_url' => $song->cover_image ? $baseUrl . '/storage/' . $song->cover_image : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $songs,
        ]);
    }

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

            if ($end === '') {
                $end = $size - 1;
            } else {
                $end = intval($end);
            }

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

    public function search(Request $request)
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $query = $request->input('q');

        $songs = Song::where('title', 'like', "%{$query}%")
                    ->orWhere('artist', 'like', "%{$query}%")
                    ->get()
                    ->map(function ($song) use ($baseUrl) {
                        return [
                            'id' => $song->id,
                            'title' => $song->title,
                            'artist' => $song->artist,
                            'audio_url' => $song->id ? $baseUrl . '/api/songs/stream/' . $song->id : null,
                            'cover_image_url' => $song->cover_image ? $baseUrl . '/storage/' . $song->cover_image : null,
                        ];
                    });

        return response()->json([
            'status' => 'success',
            'data' => $songs
        ]);
    }

    public function show(Request $request, $id)
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $song = Song::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist,
                'audio_url' => $song->file_path ? $baseUrl . '/api/songs/stream/' . basename($song->file_path) : null,
                'cover_image_url' => $song->cover_image ? $baseUrl . '/storage/' . $song->cover_image : null,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:mp3,wav|max:51200',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'album_id' => 'nullable|exists:albums,id',
            'genre' => 'required|string|max:100', // ✅ validation for genre
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers', 'public');
        }

        $filePath = $request->file('file')->store('songs', 'public');

        $song = Song::create([
            'title'      => $request->title,
            'artist'     => $request->artist,
            'file_path'  => $filePath,
            'cover_image'=> $coverPath,
            'album_id'   => $request->album_id,
            'genre'      => $request->genre, // ✅ save genre
        ]);

        $baseUrl = $request->getSchemeAndHttpHost();

        return response()->json([
            'success' => true,
            'message' => 'Song uploaded successfully',
            'data' => [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist,
                'genre' => $song->genre, // ✅ include in response
                'audio_url' => $filePath ? $baseUrl . '/storage/' . $filePath : null,
                'cover_image_url' => $coverPath ? $baseUrl . '/storage/' . $coverPath : null,
                'album_id' => $song->album_id,
            ],
        ]);
    }


    public function update(Request $request, $id)
    {
        $song = Song::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'file' => 'nullable|mimes:mp3,wav,ogg|max:10000',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'album_id' => 'nullable|exists:albums,id',
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

        $song->title = $request->title;
        $song->artist = $request->artist;
        $song->album_id = $request->album_id;
        $song->save();

        $baseUrl = $request->getSchemeAndHttpHost();

        return response()->json([
            'message' => 'Song updated successfully',
            'song' => [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist,
                'audio_url' => $song->file_path ? $baseUrl . '/storage/' . $song->file_path : null,
                'cover_image_url' => $song->cover_image ? $baseUrl . '/storage/' . $song->cover_image : null,
                'album_id' => $song->album_id,
            ]
        ]);
    }

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

        return response()->json(['message' => 'Song deleted successfully']);
    }


}
