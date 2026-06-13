<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        AppSetting::updateOrCreate(
            ['key' => 'home_greeting'],
            ['value' => 'བཀྲ་ཤིས་བདེ་ལེགས།']
        );
    }
}