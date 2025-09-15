<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class SongUploadController extends Controller
{
    /**
     * POST /api/songs
     *
     * Artist uploads a song. Uploaded songs are always created with "pending"
     * status and require admin approval before becoming publicly visible.
     *
     * Multipart form fields:
     *  - title         (required)
     *  - audio         (required file) : mp3,m4a,wav,ogg,flac
     *  - cover_image   (optional file) : jpg,jpeg,png
     *  - genre_id      (optional)
     *  - album_id      (optional)
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Ensure user has an artist record and is verified
        $artist = Artist::where('user_id', $user->id)->first();
        if (!$artist || !($artist->verified ?? false)) {
            return response()->json(['status' => 'error', 'message' => 'You must be a verified artist to upload songs'], 403);
        }

        // Validate incoming request
        $rules = [
            'title' => 'required|string|max:255',
            'audio' => 'required|file|mimes:mp3,m4a,wav,ogg,flac|max:51200', // 50MB
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB
            'genre_id' => 'nullable|integer|exists:genres,id',
            'album_id' => 'nullable|integer|exists:albums,id',
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return response()->json(['status' => 'error', 'errors' => $v->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Prepare filenames and store files on public disk
            $audio = $request->file('audio');
            $slug = Str::slug(substr($request->input('title'), 0, 80) ?: ('song-'.$user->id));
            $audioFilename = $slug . '-' . time() . '.' . $audio->getClientOriginalExtension();
            $audioPath = $audio->storeAs('songs/audio', $audioFilename, 'public');
            // compute a direct URL so client can play the uploaded file immediately
            $audioUrl = Storage::disk('public')->url($audioPath);

            $coverPath = null;
            $coverUrl = null;
            if ($request->hasFile('cover_image')) {
                $cover = $request->file('cover_image');
                $coverFilename = $slug . '-cover-' . time() . '.' . $cover->getClientOriginalExtension();
                $coverPath = $cover->storeAs('songs/covers', $coverFilename, 'public');
                $coverUrl = Storage::disk('public')->url($coverPath);
            }

            // Build attributes array only for columns that actually exist
            $data = [];

            if (Schema::hasColumn('songs', 'title')) {
                $data['title'] = $request->input('title');
            }
            if (Schema::hasColumn('songs', 'artist')) {
                $data['artist'] = $artist->display_name ?? $user->name;
            }
            if (Schema::hasColumn('songs', 'artist_id')) {
                $data['artist_id'] = $artist->id;
            }
            if (Schema::hasColumn('songs', 'file_path')) {
                $data['file_path'] = $audioPath;
            }
            if (Schema::hasColumn('songs', 'audio_url')) {
                // only write audio_url if column exists
                $data['audio_url'] = $audioUrl;
            }
            if (Schema::hasColumn('songs', 'cover_image')) {
                $data['cover_image'] = $coverPath;
            }
            // IMPORTANT: we will NOT set cover_image_url unless the column exists
            if (Schema::hasColumn('songs', 'cover_image_url') && $coverUrl !== null) {
                $data['cover_image_url'] = $coverUrl;
            }
            if (Schema::hasColumn('songs', 'genre_id')) {
                $data['genre_id'] = $request->input('genre_id');
            }
            if (Schema::hasColumn('songs', 'album_id')) {
                $data['album_id'] = $request->input('album_id');
            }

            // Always create as pending for admin review
            if (Schema::hasColumn('songs', 'status')) {
                $data['status'] = 'pending';
            }
            if (Schema::hasColumn('songs', 'is_published')) {
                // keep default false (explicitly set to 0)
                $data['is_published'] = 0;
            }

            // Create the song record
            $song = Song::create($data);

            DB::commit();

            // Return useful URLs to the client (even if not persisted)
            $payload = $song->toArray();
            $payload['_audio_url'] = $audioUrl;
            if ($coverUrl) $payload['_cover_image_url'] = $coverUrl;

            // nice friendly message for artist UX
            return response()->json([
                'status' => 'success',
                'message' => 'Song uploaded successfully and is pending admin approval. You can track its status in "My Uploads".',
                'data' => $payload,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[SongUpload] failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Upload failed'], 500);
        }
    }
}
