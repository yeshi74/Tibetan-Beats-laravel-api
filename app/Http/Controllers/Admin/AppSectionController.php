<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSection;
use App\Models\Genre;
use App\Models\Playlist;
use Illuminate\Http\Request;

class AppSectionController extends Controller
{
    public function index()
    {
        $sections = AppSection::with(['genre', 'playlist'])
            ->orderBy('page')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.app-sections.index', compact('sections'));
    }

    public function create()
    {
        $genres = Genre::orderBy('name')->get();
        $playlists = Playlist::orderBy('name')->get();

        return view('admin.app-sections.create', compact('genres', 'playlists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'page' => 'required|string',
            'title' => 'required|string|max:255',
            'section_type' => 'required|string',
            'reference_id' => 'nullable|integer',
            'genre_id' => 'nullable|exists:genres,id',
            'playlist_id' => 'nullable|exists:playlists,id',
            'sort_order' => 'required|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($validated['section_type'] === 'genre') {
            $validated['reference_id'] = $request->genre_id;
        }

        if ($validated['section_type'] === 'playlist') {
            $validated['reference_id'] = $request->playlist_id;
        }

        if (
            in_array($validated['section_type'], [
                'popular_artists',
                'latest_songs',
                'trending_songs',
                'most_liked_songs',
            ])
        ) {
            $validated['reference_id'] = null;
        }

        AppSection::create($validated);

        return redirect()
            ->route('admin.app-sections.index')
            ->with('success', 'Section created successfully.');
    }


    public function update(Request $request, AppSection $appSection)
    {
        $validated = $request->validate([
            'page' => 'required|string',
            'title' => 'required|string|max:255',
            'section_type' => 'required|string',
            'reference_id' => 'nullable|integer',
            'genre_id' => 'nullable|exists:genres,id',
            'playlist_id' => 'nullable|exists:playlists,id',
            'sort_order' => 'required|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($validated['section_type'] === 'genre') {
            $validated['reference_id'] = $request->genre_id;
        }

        if ($validated['section_type'] === 'playlist') {
            $validated['reference_id'] = $request->playlist_id;
        }

        if (
            in_array($validated['section_type'], [
                'popular_artists',
                'latest_songs',
                'trending_songs',
                'most_liked_songs',
            ])
        ) {
            $validated['reference_id'] = null;
        }

        $appSection->update($validated);

        return redirect()
            ->route('admin.app-sections.index')
            ->with('success', 'Section updated successfully.');
    }

    public function edit(AppSection $appSection)
    {
        $genres = Genre::orderBy('name')->get();
        $playlists = Playlist::orderBy('name')->get();

        return view(
            'admin.app-sections.edit',
            compact('appSection', 'genres', 'playlists')
        );
    }



    public function destroy(AppSection $appSection)
    {
        $appSection->delete();

        return redirect()
            ->route('admin.app-sections.index')
            ->with('success', 'Section deleted.');
    }
}