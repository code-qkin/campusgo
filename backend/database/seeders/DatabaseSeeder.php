<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\CampusSeeder;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([CampusSeeder::class, RouteSeeder::class]);
        $this->call([
            CampusSeeder::class,
            FutaRouteSeeder::class,
        ]);
    }
}
