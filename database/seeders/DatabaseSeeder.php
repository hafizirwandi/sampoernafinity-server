<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(FeatureSeeder::class);

        // Extra dummy users so the Pengguna list has something to paginate/search in local dev.
        User::factory(15)->create();
    }
}
