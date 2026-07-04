<?php

namespace App\Support;

class AppConfigRegistry
{
    public static function all(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | General
            |--------------------------------------------------------------------------
            */

            'app_name' => [
                'label' => 'App Name',
                'type' => 'text',
                'default' => 'TibetTune',
                'group' => 'General',

                'description' => 'Displayed inside the application.',
                'placeholder' => 'TibetTune',
                'required' => true,
            ],

            'home_greeting' => [
                'label' => 'Home Greeting',
                'type' => 'text',
                'default' => 'བཀྲ་ཤིས་བདེ་ལེགས།',
                'group' => 'General',

                'description' => 'Shown at the top of the home screen.',
                'placeholder' => 'Welcome...',
            ],

            /*
            |--------------------------------------------------------------------------
            | Feature Flags
            |--------------------------------------------------------------------------
            */

            'downloads_enabled' => [
                'label' => 'Enable Downloads',
                'type' => 'boolean',
                'default' => true,
                'group' => 'Features',
            ],

            'dedications_enabled' => [
                'label' => 'Enable Dedications',
                'type' => 'boolean',
                'default' => true,
                'group' => 'Features',
            ],

            'artist_claims_enabled' => [
                'label' => 'Enable Artist Claims',
                'type' => 'boolean',
                'default' => true,
                'group' => 'Features',
            ],

            /*
            |--------------------------------------------------------------------------
            | Limits
            |--------------------------------------------------------------------------
            */

            'free_download_limit' => [
                'label' => 'Free Download Limit',
                'type' => 'number',
                'default' => 3,
                'group' => 'Limits',
            ],

            'free_dedication_limit' => [
                'label' => 'Free Dedication Limit',
                'type' => 'number',
                'default' => 2,
                'group' => 'Limits',
            ],

            /*
            |--------------------------------------------------------------------------
            | Maintenance
            |--------------------------------------------------------------------------
            */

            'maintenance_mode' => [
                'label' => 'Maintenance Mode',
                'type' => 'boolean',
                'default' => false,
                'group' => 'Maintenance',
            ],

            'maintenance_message' => [
                'label' => 'Maintenance Message',
                'type' => 'textarea',
                'default' => 'TibetTune is currently undergoing maintenance. Please try again in a few minutes.',
                'group' => 'Maintenance',

                'description' => 'Displayed when maintenance mode is enabled.',
                'placeholder' => 'Enter maintenance message...',
            ],

            /*
            |--------------------------------------------------------------------------
            | Version Control
            |--------------------------------------------------------------------------
            */

            'minimum_version' => [
                'label' => 'Minimum App Version',
                'type' => 'text',
                'default' => '1.0.0',
                'group' => 'Version',
                'order' => 10,

                'description' => 'Users below this version must update.',
            ],

            'latest_version' => [
                'label' => 'Latest App Version',
                'type' => 'text',
                'default' => '1.0.0',
                'group' => 'Version',
                'order' => 20,

                'description' => 'Newest version currently available.',
            ],

            'force_update' => [
                'label' => 'Force Update',
                'type' => 'boolean',
                'default' => false,
                'group' => 'Version',
                'order' => 30,

                'description' => 'Require all users below the minimum version to update.',
            ],

        ];
    }
}