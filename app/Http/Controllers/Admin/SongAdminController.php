<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;
use App\Models\Album;
use App\Models\Genre;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SongAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Song::with(['album', 'genre'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist', 'like', "%{$search}%");
            });
        }

        if ($request->filled('genre_id')) {
            $query->where('genre_id', $request->genre_id);
        }

        if ($request->filled('album_id')) {
            $query->where('album_id', $request->album_id);
        }

        $songs = $query->paginate(15)->withQueryString();
        $albums = Album::all();
        $genres = \App\Models\Genre::orderBy('name')->get();

        return view('admin.songs.index', compact('songs', 'albums', 'genres'));
    }

    public function create()
    {
        $albums = Album::all();
        $genres = Genre::orderBy('name')->get();
        return view('admin.songs.create', compact('albums', 'genres'));
    }

    public function store(Request $request)
    {
        // Early check for upload-level errors (before validation).
        $err = $this->checkUploadError($request, 'file');
        if ($err !== null) {
            Log::warning('[AdminSong::store] validation failed', ['errors' => ['file' => [$err]]]);
            return redirect()->back()->withErrors(['file' => $err])->withInput();
        }
        // also check cover image upload errors if file sent
        $coverErr = $this->checkUploadError($request, 'cover_image');
        if ($coverErr !== null) {
            Log::warning('[AdminSong::store] cover_image upload failed', ['errors' => ['cover_image' => [$coverErr]]]);
            return redirect()->back()->withErrors(['cover_image' => $coverErr])->withInput();
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'artist'      => 'required|string|max:255',
            'file'        => 'required|file|mimes:mp3,wav|max:51200',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'album_id'    => 'nullable|exists:albums,id',
            'genre_id'    => 'required|exists:genres,id',
        ], [
            'file.max' => 'Audio file too large. Maximum is 50MB.',
            'file.mimes' => 'Invalid audio format. Allowed: mp3, wav.',
            'cover_image.max' => 'Cover image too large. Maximum is 2MB.',
        ]);

        $filePath = $request->file('file')->store('songs', 'public');
        $coverPath = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('covers', 'public')
            : null;

        Song::create([
            'title'       => $validated['title'],
            'artist'      => $validated['artist'],
            'file_path'   => $filePath,
            'cover_image' => $coverPath,
            'album_id'    => $validated['album_id'] ?? null,
            'genre_id'    => $validated['genre_id'],
        ]);

        return redirect()->route('admin.songs.index')
            ->with('success', 'Song uploaded successfully!');
    }

    public function edit(Song $song)
    {
        $albums = Album::all();
        $genres = Genre::orderBy('name')->get();
        return view('admin.songs.edit', compact('song', 'albums', 'genres'));
    }

    public function update(Request $request, Song $song)
    {
        // Similar early-checks could be applied here if you want.
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'artist'      => 'nullable|string|max:255',
            'album_id'    => 'nullable|exists:albums,id',
            'genre_id'    => 'required|exists:genres,id',
            'file'        => 'nullable|file|mimes:mp3,wav|max:51200',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['title', 'artist', 'album_id', 'genre_id']);

        if ($request->hasFile('cover_image')) {
            if ($song->cover_image) {
                Storage::disk('public')->delete($song->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        if ($request->hasFile('file')) {
            if ($song->file_path) {
                Storage::disk('public')->delete($song->file_path);
            }
            $data['file_path'] = $request->file('file')->store('songs', 'public');
        }

        $song->update($data);

        return redirect()->route('admin.songs.index')
            ->with('success', 'Song updated successfully!');
    }

    public function destroy(Song $song)
    {
        if ($song->file_path) {
            Storage::disk('public')->delete($song->file_path);
        }
        if ($song->cover_image) {
            Storage::disk('public')->delete($song->cover_image);
        }

        $song->delete();
        return redirect()->route('admin.songs.index')
            ->with('success', 'Song deleted.');
    }

    public function approve(Song $song)
    {
        $song->status = 'published';
        $song->save();

        return redirect()->back()->with('success', "✅ '{$song->title}' approved and published.");
    }

    public function reject(Song $song)
    {
        $song->status = 'rejected';
        $song->save();

        return redirect()->back()->with('error', "❌ '{$song->title}' rejected.");
    }

    /**
     * Detect low-level PHP file upload errors and return a friendly message
     * or null if no upload error detected (i.e. file either not present or uploaded ok).
     *
     * This helps when PHP drops the upload due to post_max_size/upload_max_filesize
     * — the Laravel validator otherwise reports "The file failed to upload."
     */
    private function checkUploadError(Request $request, string $field): ?string
    {
        // If no file input present in $_FILES, nothing to check
        if (!isset($_FILES[$field])) {
            return null;
        }

        $err = isset($_FILES[$field]['error']) ? intval($_FILES[$field]['error']) : UPLOAD_ERR_OK;
        if ($err === UPLOAD_ERR_OK) return null;

        // Map codes
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
