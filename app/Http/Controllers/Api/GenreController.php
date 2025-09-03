<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    // ✅ List all genres
    public function index()
    {
        $genres = Genre::orderBy('name')->get(['id', 'name', 'is_featured']);

            return response()->json([
                'status' => 'success',
                'genres' => $genres,
            ]);

    // ✅ Featured genres only
    }

    public function featured()
    {
        $genres = Genre::where('is_featured', true)
        ->orderBy('name')
        ->get(['id', 'name']);

        return response()->json([
            'status' => 'success',
            'genres' => $genres,
        ]);
    }
}
