<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerAdminController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('position')->paginate(12);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'nullable|string|max:255',
            'image'     => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'cta_type'  => 'required|in:none,song,playlist,album,url',
            'cta_id'    => 'nullable|integer',
            'cta_url'   => 'nullable|url',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
        ]);

        // Conditional validation
        if (in_array($data['cta_type'], ['song','playlist','album'])) {
            $request->validate(['cta_id' => 'required|integer']);
        } elseif ($data['cta_type'] === 'url') {
            $request->validate(['cta_url' => 'required|url']);
        }

        $data['image_path'] = $request->file('image')->store('banners', 'public');

        // Auto position to bottom
        $max = Banner::max('position') ?? 0;
        $data['position']  = $max + 1;
        $data['is_active'] = $request->boolean('is_active');

        Banner::create($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner created.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title'     => 'nullable|string|max:255',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'cta_type'  => 'required|in:none,song,playlist,album,url',
            'cta_id'    => 'nullable|integer',
            'cta_url'   => 'nullable|url',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
            'position'  => 'nullable|integer|min:0',
        ]);

        if (in_array($data['cta_type'], ['song','playlist','album'])) {
            $request->validate(['cta_id' => 'required|integer']);
            $data['cta_url'] = null;
        } elseif ($data['cta_type'] === 'url') {
            $request->validate(['cta_url' => 'required|url']);
            $data['cta_id']  = null;
        } else {
            $data['cta_id'] = $data['cta_url'] = null;
        }

        if ($request->hasFile('image')) {
            if ($banner->image_path) Storage::disk('public')->delete($banner->image_path);
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }

        $data['is_active'] = $request->boolean('is_active');

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image_path) Storage::disk('public')->delete($banner->image_path);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted.');
    }

    // Quick toggle active/inactive
    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);
        return back()->with('success', 'Banner status updated.');
    }

    // Move up or down (position swap)
    public function move(Request $request, Banner $banner)
    {
        $request->validate(['direction' => 'required|in:up,down']);

        $dir = $request->direction;
        $swap = $dir === 'up'
            ? Banner::where('position','<', $banner->position)->orderByDesc('position')->first()
            : Banner::where('position','>', $banner->position)->orderBy('position')->first();

        if ($swap) {
            [$banner->position, $swap->position] = [$swap->position, $banner->position];
            $banner->save();
            $swap->save();
        }

        return back();
    }
}
