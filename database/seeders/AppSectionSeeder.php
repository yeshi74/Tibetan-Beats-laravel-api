<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSection;

class AppSectionSeeder extends Seeder
{
    public function run(): void
    {
        AppSection::insert([
            [
                'page' => 'home',
                'title' => 'Popular Artists',
                'section_type' => 'popular_artists',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'page' => 'home',
                'title' => 'New Releases',
                'section_type' => 'latest_songs',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'page' => 'home',
                'title' => 'Trending Right Now',
                'section_type' => 'trending_songs',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'page' => 'home',
                'title' => 'Top Picks',
                'section_type' => 'most_liked_songs',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}