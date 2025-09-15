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
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class SongUploadController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $artist = Artist::where('user_id', $user->id)->first();
        if (!$artist || !($artist->verified ?? false)) {
            return response()->json(['status' => 'error', 'message' => 'You must be a verified artist to upload songs'], 403);
        }

        // Early detect low-level upload errors for both audio and cover
        $fileErr = $this->detectUploadErrorMessage('audio');
        if ($fileErr !== null) {
            Log::warning('[SongUpload] audio upload error: ' . $fileErr, ['userId' => $user->id]);
            return response()->json(['status' => 'error', 'message' => $fileErr], 422);
        }
        $coverErr = $this->detectUploadErrorMessage('cover_image');
        if ($coverErr !== null) {
            Log::warning('[SongUpload] cover_image upload error: ' . $coverErr, ['userId' => $user->id]);
            return response()->json(['status' => 'error', 'message' => $coverErr], 422);
        }

        $rules = [
            'title' => 'required|string|max:255',
            'audio' => 'required|file|mimes:mp3,m4a,wav,ogg,flac|max:51200',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'genre_id' => 'nullable|integer|exists:genres,id',
            'album_id' => 'nullable|integer|exists:albums,id',
            'is_published' => 'nullable|boolean',
        ];

        $v = Validator::make($request->all(), $rules, [
            'audio.max' => 'Audio file too large. Max allowed is 50MB.',
            'cover_image.max' => 'Cover image too large. Max allowed is 5MB.',
        ]);

        if ($v->fails()) {
            Log::warning('[SongUpload] validation failed', ['errors' => $v->errors()]);
            return response()->json(['status' => 'error', 'errors' => $v->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $audio = $request->file('audio');
            $slug = Str::slug(substr($request->input('title'), 0, 80) ?: ('song-'.$user->id));
            $audioFilename = $slug . '-' . time() . '.' . $audio->getClientOriginalExtension();
            $audioPath = $audio->storeAs('songs/audio', $audioFilename, 'public');
            $audioUrl = Storage::disk('public')->url($audioPath);

            $coverPath = null;
            $coverUrl = null;
            if ($request->hasFile('cover_image')) {
                $cover = $request->file('cover_image');
                $coverFilename = $slug . '-cover-' . time() . '.' . $cover->getClientOriginalExtension();
                $coverPath = $cover->storeAs('songs/covers', $coverFilename, 'public');
                $coverUrl = Storage::disk('public')->url($coverPath);
            }

            // Build data array conditionally depending on which columns actually exist
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
                $data['audio_url'] = $audioUrl;
            }
            if (Schema::hasColumn('songs', 'cover_image')) {
                $data['cover_image'] = $coverPath;
            }
            if (Schema::hasColumn('songs', 'cover_image_url') && $coverUrl !== null) {
                $data['cover_image_url'] = $coverUrl;
            }
            if (Schema::hasColumn('songs', 'genre_id')) {
                $data['genre_id'] = $request->input('genre_id');
            }
            if (Schema::hasColumn('songs', 'album_id')) {
                $data['album_id'] = $request->input('album_id');
            }
            if (Schema::hasColumn('songs', 'status')) {
                // By default mark pending for moderation; ignore client is_published for artists
                $data['status'] = 'pending';
            }
            if (Schema::hasColumn('songs', 'is_published')) {
                // keep false by default
                $data['is_published'] = 0;
            }

            $song = Song::create($data);

            DB::commit();

            $payload = $song->toArray();
            $payload['_audio_url'] = $audioUrl;
            if ($coverUrl) $payload['_cover_image_url'] = $coverUrl;

            return response()->json([
                'status' => 'success',
                'data' => $payload,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[SongUpload] failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Upload failed'], 500);
        }
    }

    /**
     * Helper: detect PHP upload errors for a given field and return user-friendly message or null.
     */
    private function detectUploadErrorMessage(string $field): ?string
    {
        if (!isset($_FILES[$field])) {
            return null;
        }
        $err = isset($_FILES[$field]['error']) ? intval($_FILES[$field]['error']) : UPLOAD_ERR_OK;
        if ($err === UPLOAD_ERR_OK) return null;

        $map = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the server\'s maximum allowed size. Increase upload_max_filesize/post_max_size or upload a smaller file.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the form maximum size.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded. Please try again.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server misconfiguration: missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Server error: failed to write uploaded file.',
            UPLOAD_ERR_EXTENSION  => 'File upload stopped by a PHP extension.',
        ];

        return $map[$err] ?? 'Unknown upload error (code: ' . $err . ').';
    }
}
