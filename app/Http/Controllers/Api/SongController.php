<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;

class SongAdminController extends Controller
{
    public function create()
    {
        return view('admin.songs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'artist' => 'nullable|string',
            'file' => 'required|mimes:mp3,wav|max:20000',
        ]);

        $filePath = $request->file('file')->store('songs', 'public');

        Song::create([
            'title' => $request->title,
            'artist' => $request->artist,
            'file_path' => $filePath,
        ]);

        return redirect()->route('admin.songs')->with('success', 'Song uploaded successfully!');
    }
}

