<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Route;
use App\Models\RouteStop;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $route = Route::create([
            'campus_id' => 1,
            'name' => 'Main Gate → Engineering',
            'is_active' => true,
        ]);

        RouteStop::create(['route_id' => $route->id, 'name' => 'Main Gate', 'order' => 1, 'lat' => 6.5244, 'lng' => 3.3792]);
        RouteStop::create(['route_id' => $route->id, 'name' => 'New Hostel', 'order' => 2, 'lat' => 6.5250, 'lng' => 3.3800]);
        RouteStop::create(['route_id' => $route->id, 'name' => 'Engineering', 'order' => 3, 'lat' => 6.5260, 'lng' => 3.3810]);

        $route2 = Route::create([
            'campus_id' => 1,
            'name' => 'Main Gate → soc',
            'is_active' => true,
        ]);

        RouteStop::create(['route_id' => $route2->id, 'name' => 'Main Gate', 'order' => 1, 'lat' => 6.5244, 'lng' => 3.3792]);
        RouteStop::create(['route_id' => $route2->id, 'name' => 'engineering', 'order' => 2, 'lat' => 6.5250, 'lng' => 3.3800]);
        RouteStop::create(['route_id' => $route2->id, 'name' => 'soc', 'order' => 3, 'lat' => 6.5260, 'lng' => 3.3810]);

        $route3 = Route::create([
            'campus_id' => 1,
            'name' => 'west gate → vc lodge',
            'is_active' => true,

        ]);

        RouteStop::create(['route_id' => $route3->id, 'name' => 'Main Gate', 'order' => 1, 'lat' => 6.5204, 'lng' => 3.3792]);
        RouteStop::create(['route_id' => $route3->id, 'name' => 'New Hostel', 'order' => 2, 'lat' => 6.5240, 'lng' => 3.3800]);
        RouteStop::create(['route_id' => $route3->id, 'name' => 'Engineering', 'order' => 3, 'lat' => 6.5960, 'lng' => 3.3810]);
    }
}
