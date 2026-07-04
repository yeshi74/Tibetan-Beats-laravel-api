<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use App\Support\AppConfigRegistry;

class AppSettingController extends Controller
{
    public function edit()
    {
        $settings = AppSetting::pluck('value', 'key');

        $registry = AppConfigRegistry::all();

        $groupedRegistry = collect($registry)
            ->groupBy('group', preserveKeys: true);

        return view(
            'admin.settings.edit',
            compact(
                'settings',
                'groupedRegistry'
            )
        );
    }

    public function update(Request $request)
    {
        foreach (AppConfigRegistry::all() as $key => $setting) {

            $value = match ($setting['type']) {

                'boolean' => $request->has($key),

                default => $request->input(
                    $key,
                    $setting['default']
                ),
            };

            AppSetting::set(
                $key,
                $value
            );
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'Settings updated successfully.'
            );
    }


}