<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Campus;

class FutaRouteSeeder extends Seeder
{
    public function run(): void
    {
        $campus = Campus::where('slug', 'futa')->first();
        if (!$campus) return;

        $routes = [
            [
                'name' => 'Main Gate → Engineering',
                'stops' => [
                    ['name' => 'Main Gate',            'order' => 1, 'lat' => 7.2981, 'lng' => 5.1401],
                    ['name' => 'Senate Building',      'order' => 2, 'lat' => 7.3002, 'lng' => 5.1388],
                    ['name' => 'School of Sciences',   'order' => 3, 'lat' => 7.3018, 'lng' => 5.1375],
                    ['name' => 'Engineering Complex',  'order' => 4, 'lat' => 7.3037, 'lng' => 5.1362],
                ]
            ],
            [
                'name' => 'Hostels → Agriculture',
                'stops' => [
                    ['name' => 'Post Graduate Hostel',  'order' => 1, 'lat' => 7.3055, 'lng' => 5.1420],
                    ['name' => 'Sports Complex',        'order' => 2, 'lat' => 7.3045, 'lng' => 5.1405],
                    ['name' => 'Health Centre',         'order' => 3, 'lat' => 7.3030, 'lng' => 5.1395],
                    ['name' => 'School of Agriculture', 'order' => 4, 'lat' => 7.3010, 'lng' => 5.1410],
                ]
            ],
            [
                'name' => 'New Hostel → Library',
                'stops' => [
                    ['name' => 'New Hostel',         'order' => 1, 'lat' => 7.3060, 'lng' => 5.1430],
                    ['name' => 'SICT Building',      'order' => 2, 'lat' => 7.3048, 'lng' => 5.1415],
                    ['name' => 'SUB Junction',       'order' => 3, 'lat' => 7.3035, 'lng' => 5.1400],
                    ['name' => 'University Library', 'order' => 4, 'lat' => 7.3020, 'lng' => 5.1385],
                ]
            ],
            [
                'name' => 'Main Gate → Library',
                'stops' => [
                    ['name' => 'Main Gate',          'order' => 1, 'lat' => 7.2981, 'lng' => 5.1401],
                    ['name' => 'Admin Block',        'order' => 2, 'lat' => 7.2995, 'lng' => 5.1393],
                    ['name' => 'SUB Junction',       'order' => 3, 'lat' => 7.3035, 'lng' => 5.1400],
                    ['name' => 'University Library', 'order' => 4, 'lat' => 7.3020, 'lng' => 5.1385],
                ]
            ],
            [
                'name' => 'Engineering → Hostels',
                'stops' => [
                    ['name' => 'Engineering Complex',   'order' => 1, 'lat' => 7.3037, 'lng' => 5.1362],
                    ['name' => 'School of Sciences',    'order' => 2, 'lat' => 7.3018, 'lng' => 5.1375],
                    ['name' => 'Sports Complex',        'order' => 3, 'lat' => 7.3045, 'lng' => 5.1405],
                    ['name' => 'Post Graduate Hostel',  'order' => 4, 'lat' => 7.3055, 'lng' => 5.1420],
                ]
            ],
            [
                'name' => 'Main Gate → Health Centre',
                'stops' => [
                    ['name' => 'Main Gate',      'order' => 1, 'lat' => 7.2981, 'lng' => 5.1401],
                    ['name' => 'Senate Building','order' => 2, 'lat' => 7.3002, 'lng' => 5.1388],
                    ['name' => 'SUB Junction',   'order' => 3, 'lat' => 7.3035, 'lng' => 5.1400],
                    ['name' => 'Health Centre',  'order' => 4, 'lat' => 7.3030, 'lng' => 5.1395],
                ]
            ],
            [
                'name' => 'Library → Agriculture',
                'stops' => [
                    ['name' => 'University Library',    'order' => 1, 'lat' => 7.3020, 'lng' => 5.1385],
                    ['name' => 'SUB Junction',          'order' => 2, 'lat' => 7.3035, 'lng' => 5.1400],
                    ['name' => 'Health Centre',         'order' => 3, 'lat' => 7.3030, 'lng' => 5.1395],
                    ['name' => 'School of Agriculture', 'order' => 4, 'lat' => 7.3010, 'lng' => 5.1410],
                ]
            ],
            [
                'name' => 'New Hostel → Engineering',
                'stops' => [
                    ['name' => 'New Hostel',           'order' => 1, 'lat' => 7.3060, 'lng' => 5.1430],
                    ['name' => 'Post Graduate Hostel', 'order' => 2, 'lat' => 7.3055, 'lng' => 5.1420],
                    ['name' => 'Senate Building',      'order' => 3, 'lat' => 7.3002, 'lng' => 5.1388],
                    ['name' => 'Engineering Complex',  'order' => 4, 'lat' => 7.3037, 'lng' => 5.1362],
                ]
            ],
            [
                'name' => 'Sports Complex → Library',
                'stops' => [
                    ['name' => 'Sports Complex',     'order' => 1, 'lat' => 7.3045, 'lng' => 5.1405],
                    ['name' => 'Health Centre',      'order' => 2, 'lat' => 7.3030, 'lng' => 5.1395],
                    ['name' => 'SICT Building',      'order' => 3, 'lat' => 7.3048, 'lng' => 5.1415],
                    ['name' => 'University Library', 'order' => 4, 'lat' => 7.3020, 'lng' => 5.1385],
                ]
            ],
            [
                'name' => 'Main Gate → Full Campus Loop',
                'stops' => [
                    ['name' => 'Main Gate',            'order' => 1, 'lat' => 7.2981, 'lng' => 5.1401],
                    ['name' => 'Senate Building',      'order' => 2, 'lat' => 7.3002, 'lng' => 5.1388],
                    ['name' => 'Engineering Complex',  'order' => 3, 'lat' => 7.3037, 'lng' => 5.1362],
                    ['name' => 'University Library',   'order' => 4, 'lat' => 7.3020, 'lng' => 5.1385],
                    ['name' => 'Health Centre',        'order' => 5, 'lat' => 7.3030, 'lng' => 5.1395],
                    ['name' => 'Sports Complex',       'order' => 6, 'lat' => 7.3045, 'lng' => 5.1405],
                    ['name' => 'Post Graduate Hostel', 'order' => 7, 'lat' => 7.3055, 'lng' => 5.1420],
                    ['name' => 'New Hostel',           'order' => 8, 'lat' => 7.3060, 'lng' => 5.1430],
                ]
            ],
        ];

        foreach ($routes as $routeData) {
            $route = Route::create([
                'campus_id' => $campus->id,
                'name'      => $routeData['name'],
                'is_active' => true,
            ]);

            foreach ($routeData['stops'] as $stop) {
                RouteStop::create([
                    'route_id' => $route->id,
                    'name'     => $stop['name'],
                    'order'    => $stop['order'],
                    'lat'      => $stop['lat'],
                    'lng'      => $stop['lng'],
                ]);
            }
        }
    }
}