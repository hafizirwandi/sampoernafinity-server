<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Master list of features the app offers. This is fixed, seed-only data —
     * there is no create/delete UI for it, only an admin toggle for whether a
     * feature ships free by default. Add new features here as the product grows.
     */
    public function run(): void
    {
        $features = [
            ['key' => 'interaksi-aktivitas', 'name' => 'Interaksi & aktivitas'],
            ['key' => 'preset-stream-profile', 'name' => 'Preset (stream profile)'],
            ['key' => 'server-tiktok-premium', 'name' => 'Server TikTok premium'],
            ['key' => 'soundboard', 'name' => 'Soundboard'],
            ['key' => 'preset-soundboard', 'name' => 'Preset soundboard'],
            ['key' => 'avatar-terpasang', 'name' => 'Avatar terpasang'],
            ['key' => 'avatar-premium', 'name' => 'Avatar premium'],
            ['key' => 'playlist-lagu', 'name' => 'Playlist lagu'],
            ['key' => 'lagu-per-playlist', 'name' => 'Lagu per playlist'],
            ['key' => 'spin-roulette', 'name' => 'Spin Roulette'],
            ['key' => 'game-eliminasi', 'name' => 'Game Eliminasi'],
            ['key' => 'auction-lelang', 'name' => 'Auction (lelang)'],
            ['key' => 'gifter-winner', 'name' => 'Gifter Winner'],
        ];

        foreach ($features as $index => $feature) {
            Feature::updateOrCreate(
                ['key' => $feature['key']],
                ['name' => $feature['name'], 'sort_order' => $index]
            );
        }
    }
}
