<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;

class AppSettingController extends Controller
{
    public function index()
    {
        return response()->json([

            // General
            'app_name' => AppSetting::get(
                'app_name',
                'TibetTune'
            ),

            'home_greeting' => AppSetting::get(
                'home_greeting',
                'Welcome to TibetTune'
            ),

            // Feature Flags
            'downloads_enabled' => AppSetting::getBool(
                'downloads_enabled',
                true
            ),

            'dedications_enabled' => AppSetting::getBool(
                'dedications_enabled',
                true
            ),

            'artist_claims_enabled' => AppSetting::getBool(
                'artist_claims_enabled',
                true
            ),

            // Limits
            'free_download_limit' => AppSetting::getInt(
                'free_download_limit',
                3
            ),

            'free_dedication_limit' => AppSetting::getInt(
                'free_dedication_limit',
                2
            ),

            // Maintenance
            'maintenance_mode' => AppSetting::getBool(
                'maintenance_mode',
                false
            ),

            'maintenance_message' => AppSetting::get(
                'maintenance_message',
                'TibetTune is currently undergoing maintenance.'
            ),

            'minimum_version' => AppSetting::get(
                'minimum_version',
                '1.0.0'
            ),

            'latest_version' => AppSetting::get(
                'latest_version',
                '1.0.0'
            ),

            'force_update' => AppSetting::getBool(
                'force_update',
                false
            ),
        ]);
    }
}