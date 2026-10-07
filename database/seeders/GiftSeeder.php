<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GiftSeeder extends Seeder
{
    /**
     * Import the master TikTok gift catalog from data/tiktok_gift.json.
     * Upserted in chunks keyed by (type, tiktok_id) so re-running the
     * seeder just refreshes names/coin/image instead of duplicating rows.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/tiktok_gift.json');

        $gifts = json_decode(file_get_contents($path), true);

        $now = now();

        collect($gifts)
            ->map(fn (array $gift) => [
                'gift_category_id' => null,
                'type' => 'gift',
                'tiktok_id' => $gift['tiktok_gift_id'],
                'name' => $gift['name'],
                'coin' => $gift['diamond_count'],
                'image_url' => $gift['icon_url'],
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->chunk(500)
            ->each(function ($chunk) {
                DB::table('gifts')->upsert(
                    $chunk->all(),
                    ['type', 'tiktok_id'],
                    ['name', 'coin', 'image_url', 'updated_at']
                );
            });
    }
}
