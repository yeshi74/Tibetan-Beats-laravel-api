<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;

class AppSettingController extends Controller
{
    public function index()
    {
        return response()->json([
            'home_greeting' => AppSetting::where(
                'key',
                'home_greeting'
            )->value('value'),
        ]);
    }
}