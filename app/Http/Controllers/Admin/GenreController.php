<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::latest()->paginate(10);
        return view('admin.genres.index', compact('genres'));
    }

    public function create()
    {
        return view('admin.genres.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:genres',
            'description' => 'nullable|string',
            'is_featured' => 'nullable|boolean', // ✅ allow checkbox (missing means null)
        ]);

        $validated['is_featured'] = $request->has('is_featured'); // ✅ fix checkbox

        Genre::create($validated);

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Genre created successfully!');
    }

    public function edit(Genre $genre)
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:genres,name,' . $genre->id,
            'description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
        ]);

        $validated['is_featured'] = $request->has('is_featured'); // ✅ fix checkbox

        $genre->update($validated);

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Genre updated successfully!');
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Genre deleted.');
    }
}
