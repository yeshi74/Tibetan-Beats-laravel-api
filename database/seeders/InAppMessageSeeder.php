<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\InAppMessage;

class InAppMessageSeeder extends Seeder {
    public function run() {
        InAppMessage::updateOrCreate(
            ['key' => 'welcome-2025'],
            [
                'type' => 'modal',
                'title' => ['en' => 'Welcome to TibetTune'],
                'body'  => ['en' => 'Check out our festival playlist and new features.'],
                'image_url' => null,
                'cta' => ['type'=>'route','value'=>'/playlists/festival','label'=>['en'=>'Explore']],
                'audience' => 'all',
                'priority' => 100,
                'status' => 'active'
            ]
        );
    }
}
