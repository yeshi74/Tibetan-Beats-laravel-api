<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mood;

class MoodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $moods = Mood::withCount('songs')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->paginate(20);

        return view('admin.moods.index', compact('moods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.moods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:moods,name',
            'emoji' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
            'start_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'end_color'   => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        Mood::create($validated);

        return redirect()
            ->route('admin.moods.index')
            ->with('success', 'Mood created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mood $mood)
    {
        return view('admin.moods.edit', compact('mood'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mood $mood)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:moods,name,' . $mood->id,
            'emoji' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',

            'start_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'end_color'   => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $mood->update($validated);

        return redirect()
            ->route('admin.moods.index')
            ->with('success', 'Mood updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mood $mood)
    {
        if ($mood->songs()->exists()) {
            return redirect()
                ->route('admin.moods.index')
                ->with('error', 'This mood is assigned to one or more songs and cannot be deleted.');
        }

        $mood->delete();

        return redirect()
            ->route('admin.moods.index')
            ->with('success', 'Mood deleted successfully!');
    }

}
