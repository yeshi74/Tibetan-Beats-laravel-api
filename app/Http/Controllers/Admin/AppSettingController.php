<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function edit()
    {
        $settings = AppSetting::pluck('value', 'key');

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        AppSetting::updateOrCreate(
            ['key' => 'home_greeting'],
            ['value' => $request->home_greeting]
        );

        return redirect()
            ->back()
            ->with('success', 'Settings updated successfully.');
    }
}