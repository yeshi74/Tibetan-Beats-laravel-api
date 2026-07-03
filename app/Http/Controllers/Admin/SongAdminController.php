<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;
use App\Models\Album;
use App\Models\Genre;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Artist;
use App\Models\Notification;

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

        if ($request->filled('visibility')) {
            $query->where('is_visible', (bool) $request->visibility);
        }

        $songs = $query->paginate(15)->withQueryString();
        $stats = [
            'total' => Song::count(),
            'visible' => Song::where('is_visible', true)->count(),
            'hidden' => Song::where('is_visible', false)->count(),
            'published' => Song::where('status', 'published')->count(),
            'pending' => Song::where('status', 'pending')->count(),
        ];
        $albums = Album::all();
        $genres = \App\Models\Genre::orderBy('name')->get();

        return view(
            'admin.songs.index',
            compact(
                'songs',
                'albums',
                'genres',
                'stats'
            )
        );
    }

    public function create()
    {
        $albums = Album::all();
        $genres = Genre::orderBy('name')->get();
        $artists = Artist::orderBy('display_name')->get();

        return view(
            'admin.songs.create',
            compact('albums', 'genres', 'artists')
        );
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
            'artist_id'   => 'nullable|exists:artists,id',
            'file'        => 'required|file|mimes:mp3,wav|max:51200',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'album_id'    => 'nullable|exists:albums,id',
            'genre_id'    => 'required|exists:genres,id',
            'is_visible' => 'required|in:0,1',
        ], [
            'file.max' => 'Audio file too large. Maximum is 50MB.',
            'file.mimes' => 'Invalid audio format. Allowed: mp3, wav.',
            'cover_image.max' => 'Cover image too large. Maximum is 2MB.',
        ]);

        $filePath = $request->file('file')->store('songs', 'public');
        $coverPath = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('covers', 'public')
            : null;

            $artist = Artist::findOrFail($validated['artist_id']);

        Song::create([
            'title'       => $validated['title'],
            'artist_id' => $artist->id,
            'artist' => $artist->display_name,
            'file_path'   => $filePath,
            'cover_image' => $coverPath,
            'album_id'    => $validated['album_id'] ?? null,
            'genre_id'    => $validated['genre_id'],
            'is_visible' => (bool) $validated['is_visible'],
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
            'is_visible' => 'required|in:0,1',
            'file'        => 'nullable|file|mimes:mp3,wav|max:51200',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['title', 'artist', 'album_id', 'genre_id', 'is_visible']);

        $data['is_visible'] = (bool) $request->is_visible;

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
        $song->rejection_reason = null;

        if (\Schema::hasColumn('songs', 'is_published')) {
            $song->is_published = true;
        }

        $song->save();

        $artist = Artist::find($song->artist_id);

        if ($artist) {
            foreach ($artist->followers as $follower) {
                Notification::create([
                    'user_id' => $follower->user_id,

                    'title' =>
                        $artist->display_name .
                        ' released a new song',

                    'body' => $song->title,

                    'type' => 'artist_song',

                    'data' => [
                        'song_id' => $song->id,
                        'artist_id' => $artist->id,
                    ],

                    'is_read' => false,
                ]);
            }
        }

        return redirect()
            ->back()
            ->with(
                'success',
                "✅ '{$song->title}' approved and published."
            );
    }

    public function reject(Request $request, Song $song)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $song->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()
            ->back()
            ->with(
                'error',
                "❌ '{$song->title}' rejected."
            );
    }

    public function toggleVisibility(Song $song)
    {
        $song->update([
            'is_visible' => !$song->is_visible,
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                "'{$song->title}' is now " .
                ($song->is_visible ? 'Visible' : 'Hidden') .
                ' in the mobile app.'
            );
    }

    public function bulkVisibility(Request $request)
    {
        $validated = $request->validate([
            'song_ids' => 'required|array|min:1',
            'song_ids.*' => 'exists:songs,id',
            'is_visible' => 'required|boolean',
        ]);

        Song::whereIn('id', $validated['song_ids'])
            ->update([
                'is_visible' => $validated['is_visible'],
            ]);

        return back()->with(
            'success',
            count($validated['song_ids']) . ' song(s) updated successfully.'
        );
    }

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
