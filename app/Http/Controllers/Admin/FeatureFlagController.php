<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    public function index() {
        return view('admin.feature-flags.index', [
            'flags' => FeatureFlag::orderBy('name')->get(),
        ]);
    }

    public function toggle(\App\Models\FeatureFlag $flag)
    {
        $flag->enabled = ! $flag->enabled;
        $flag->save();

        // Clear cache so change is immediate
        \App\Support\Feature::clearCache($flag->key);

        return back()->with('status', "Feature '{$flag->name}' toggled.");
    }

    public function store(Request $req) {
        $req->validate([
            'key'  => 'required|string|unique:feature_flags,key',
            'name' => 'required|string',
        ]);

        FeatureFlag::create($req->only('key','name'));

        return back()->with('status','Feature flag created');
    }
}
