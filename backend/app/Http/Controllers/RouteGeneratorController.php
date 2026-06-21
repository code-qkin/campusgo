<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\CampusStop;
use App\Models\Campus;

class RouteGeneratorController extends Controller
{
    // FUTA bounding box
    const FUTA_SOUTH = 7.295;
    const FUTA_WEST = 5.135;
    const FUTA_NORTH = 7.310;
    const FUTA_EAST = 5.145;

    public function generate(Request $request)
    {
        $campus = Campus::where('slug', 'futa')->firstOrFail();

        // Step 1 — query Overpass for all named nodes inside FUTA
        $query = '[out:json][timeout:25];(node["name"](7.295,5.135,7.310,5.145););out body;';
        // $query = '[out:json][timeout:25];
        // (
        //   node["name"](' . self::FUTA_SOUTH . ',' . self::FUTA_WEST . ',' . self::FUTA_NORTH . ',' . self::FUTA_EAST . ');
        //   node["amenity"](' . self::FUTA_SOUTH . ',' . self::FUTA_WEST . ',' . self::FUTA_NORTH . ',' . self::FUTA_EAST . ');
        //   node["building"](' . self::FUTA_SOUTH . ',' . self::FUTA_WEST . ',' . self::FUTA_NORTH . ',' . self::FUTA_EAST . ');
        // );
        // out body;';

        $response = Http::timeout(60)
            ->withHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'User-Agent' => 'CampusGo/1.0'
            ])
            ->get('https://overpass-api.de/api/interpreter', [
                'data' => '[out:json][timeout:25];node["name"](7.295,5.135,7.310,5.145);out body;'
            ]);

        $nodes = collect($response->json('elements'))
            ->filter(fn($n) => isset($n['tags']['name']))
            ->filter(fn($n) => $this->isSignificant($n))
            ->values();

        if ($nodes->isEmpty()) {
            return response()->json(['message' => 'No significant locations found'], 404);
        }

        // Step 2 — cluster nearby nodes (merge duplicates within 50m)
        $stops = $this->clusterNodes($nodes->toArray());

        // Step 3 — generate routes between stops
        $routes = $this->generateRoutes($stops, $campus->id);

        return response()->json([
            'message' => 'Routes generated successfully',
            'stops_found' => count($stops),
            'routes_created' => count($routes),
            'routes' => $routes,
        ]);
    }

    private function isSignificant(array $node): bool
    {
        $tags = $node['tags'] ?? [];
        $name = strtolower($tags['name'] ?? '');

        // keep gates, buildings, hostels, academic blocks, common areas
        $keywords = [
            'gate',
            'hostel',
            'hall',
            'complex',
            'block',
            'centre',
            'center',
            'library',
            'clinic',
            'hospital',
            'school',
            'faculty',
            'department',
            'junction',
            'field',
            'stadium',
            'auditorium',
            'senate',
            'admin',
            'sict',
            'sub',
            'sport',
            'laboratory',
            'lab',
            'lecture'
        ];

        foreach ($keywords as $kw) {
            if (str_contains($name, $kw))
                return true;
        }

        // also keep if it has amenity or building tag
        if (isset($tags['amenity']) || isset($tags['building']))
            return true;

        return false;
    }

    private function clusterNodes(array $nodes): array
    {
        $clusters = [];

        foreach ($nodes as $node) {
            $merged = false;
            foreach ($clusters as &$cluster) {
                $dist = $this->haversine(
                    $node['lat'],
                    $node['lon'],
                    $cluster['lat'],
                    $cluster['lon']
                );
                if ($dist < 50) { // merge if within 50 metres
                    // keep the one with a better name
                    if (strlen($node['tags']['name']) > strlen($cluster['name'])) {
                        $cluster['name'] = $node['tags']['name'];
                    }
                    $merged = true;
                    break;
                }
            }
            if (!$merged) {
                $clusters[] = [
                    'name' => $node['tags']['name'],
                    'lat' => $node['lat'],
                    'lon' => $node['lon'],
                ];
            }
        }

        return $clusters;
    }

    private function generateRoutes(array $stops, int $campusId): array
    {
        $created = [];
        $count = count($stops);

        // sort stops by longitude (west to east) to create logical routes
        usort($stops, fn($a, $b) => $a['lon'] <=> $b['lon']);

        // generate routes between stops that are 200m - 2km apart
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $dist = $this->haversine(
                    $stops[$i]['lat'],
                    $stops[$i]['lon'],
                    $stops[$j]['lat'],
                    $stops[$j]['lon']
                );

                // only create routes between 200m and 2000m apart
                if ($dist < 200 || $dist > 2000)
                    continue;

                // find intermediate stops along the path
                $waypoints = $this->getWaypoints($stops, $i, $j);

                $routeName = $stops[$i]['name'] . ' → ' . $stops[$j]['name'];

                // skip if route already exists
                if (Route::where('name', $routeName)->where('campus_id', $campusId)->exists()) {
                    continue;
                }

                $route = Route::create([
                    'campus_id' => $campusId,
                    'name' => $routeName,
                    'is_active' => true,
                ]);

                $order = 1;
                RouteStop::create([
                    'route_id' => $route->id,
                    'name' => $stops[$i]['name'],
                    'order' => $order++,
                    'lat' => $stops[$i]['lat'],
                    'lng' => $stops[$i]['lon'],
                ]);

                foreach ($waypoints as $wp) {
                    RouteStop::create([
                        'route_id' => $route->id,
                        'name' => $wp['name'],
                        'order' => $order++,
                        'lat' => $wp['lat'],
                        'lng' => $wp['lon'],
                    ]);
                }

                RouteStop::create([
                    'route_id' => $route->id,
                    'name' => $stops[$j]['name'],
                    'order' => $order++,
                    'lat' => $stops[$j]['lat'],
                    'lng' => $stops[$j]['lon'],
                ]);

                $created[] = $routeName;

                // limit to 30 routes max
                if (count($created) >= 30)
                    break 2;
            }
        }

        return $created;
    }

    private function getWaypoints(array $stops, int $from, int $to): array
    {
        $waypoints = [];
        $fromStop = $stops[$from];
        $toStop = $stops[$to];

        // find stops geographically between from and to
        foreach ($stops as $i => $stop) {
            if ($i === $from || $i === $to)
                continue;

            // check if stop is roughly between from and to
            $minLat = min($fromStop['lat'], $toStop['lat']);
            $maxLat = max($fromStop['lat'], $toStop['lat']);
            $minLon = min($fromStop['lon'], $toStop['lon']);
            $maxLon = max($fromStop['lon'], $toStop['lon']);

            $padding = 0.001; // ~100m padding
            if (
                $stop['lat'] >= $minLat - $padding &&
                $stop['lat'] <= $maxLat + $padding &&
                $stop['lon'] >= $minLon - $padding &&
                $stop['lon'] <= $maxLon + $padding
            ) {
                $waypoints[] = $stop;
            }
        }

        // sort waypoints by distance from start
        usort($waypoints, function ($a, $b) use ($fromStop) {
            $da = $this->haversine($fromStop['lat'], $fromStop['lon'], $a['lat'], $a['lon']);
            $db = $this->haversine($fromStop['lat'], $fromStop['lon'], $b['lat'], $b['lon']);
            return $da <=> $db;
        });

        return $waypoints;
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371000; // metres
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function generateFromStops(Request $request)
{
    $campus = Campus::where('slug', 'futa')->firstOrFail();

    $stops = CampusStop::where('campus_id', $campus->id)->get();

    if ($stops->count() < 2) {
        return response()->json(['message' => 'Add at least 2 stops first'], 400);
    }

    // delete existing auto-generated routes first
    $existingRoutes = Route::where('campus_id', $campus->id)->get();
    foreach ($existingRoutes as $route) {
        $stopIds = $route->stops()->pluck('id')->toArray();
        if (!empty($stopIds)) {
            \App\Models\CarpoolPassenger::whereIn('boarding_stop_id', $stopIds)
                ->orWhereIn('exit_stop_id', $stopIds)->delete();
        }
        \App\Models\CarpoolPassenger::whereHas('ride', fn($q) =>
            $q->where('route_id', $route->id)
        )->delete();
        \App\Models\CarpoolRide::where('route_id', $route->id)->delete();
        \App\Models\RouteSegment::where('route_id', $route->id)->delete();
        $route->stops()->delete();
        $route->delete();
    }

    // get road network from Overpass (cached for 30 days)
    $cacheKey = 'futa_road_network';
    $roadNetwork = \Cache::get($cacheKey);

    if (!$roadNetwork) {
        $bbox = '7.290,5.130,7.315,5.150';
        $query = '[out:json][timeout:30];(way["highway"](' . $bbox . '););out geom;';

        $response = Http::timeout(60)
            ->withHeaders(['User-Agent' => 'CampusGo/1.0'])
            ->get('https://overpass-api.de/api/interpreter', ['data' => $query]);

        if (!$response->ok()) {
            return response()->json(['message' => 'Failed to fetch road network'], 500);
        }

        $roadNetwork = $response->json('elements') ?? [];
        \Cache::put($cacheKey, $roadNetwork, now()->addDays(30));
    }

    // for each stop find nearest roads
    $stopRoadMap = [];
    foreach ($stops as $stop) {
        $nearestRoads = [];
        foreach ($roadNetwork as $road) {
            if (!isset($road['geometry'])) continue;
            foreach ($road['geometry'] as $point) {
                $dist = $this->haversine($stop->lat, $stop->lng, $point['lat'], $point['lon']);
                if ($dist <= 80) {
                    $nearestRoads[] = $road['id'];
                    break;
                }
            }
        }
        $stopRoadMap[$stop->id] = array_unique($nearestRoads);
    }

    $created    = [];
    $stopsArray = $stops->toArray();
    $count      = count($stopsArray);

    for ($i = 0; $i < $count; $i++) {
        for ($j = $i + 1; $j < $count; $j++) {
            $stopA = $stopsArray[$i];
            $stopB = $stopsArray[$j];

            $roadsA = $stopRoadMap[$stopA['id']] ?? [];
            $roadsB = $stopRoadMap[$stopB['id']] ?? [];

            $sharedRoads = array_intersect($roadsA, $roadsB);
            $dist = $this->haversine($stopA['lat'], $stopA['lng'], $stopB['lat'], $stopB['lng']);

            if (empty($sharedRoads) && $dist > 1500) continue;
            if ($dist < 100 || $dist > 3000) continue;

            $routeName = $stopA['name'] . ' → ' . $stopB['name'];

            if (Route::where('name', $routeName)->where('campus_id', $campus->id)->exists()) continue;

            $intermediates = $this->findIntermediateStops($stopsArray, $i, $j, $stopRoadMap);

            $route = Route::create([
                'campus_id' => $campus->id,
                'name'      => $routeName,
                'is_active' => true,
            ]);

            $order     = 1;
            $usedNames = [$stopA['name']];

            RouteStop::create([
                'route_id' => $route->id,
                'name'     => $stopA['name'],
                'order'    => $order++,
                'lat'      => $stopA['lat'],
                'lng'      => $stopA['lng'],
            ]);

            foreach ($intermediates as $mid) {
                if (in_array($mid['name'], $usedNames)) continue;
                $usedNames[] = $mid['name'];

                RouteStop::create([
                    'route_id' => $route->id,
                    'name'     => $mid['name'],
                    'order'    => $order++,
                    'lat'      => $mid['lat'],
                    'lng'      => $mid['lng'],
                ]);
            }

            if (!in_array($stopB['name'], $usedNames)) {
                RouteStop::create([
                    'route_id' => $route->id,
                    'name'     => $stopB['name'],
                    'order'    => $order,
                    'lat'      => $stopB['lat'],
                    'lng'      => $stopB['lng'],
                ]);
            }

            $created[] = $routeName;
            if (count($created) >= 50) break 2;
        }
    }

    return response()->json([
        'message'        => 'Routes generated from campus stops',
        'stops_used'     => $stops->count(),
        'roads_found'    => count($roadNetwork),
        'routes_created' => count($created),
        'routes'         => $created,
    ]);
}

    private function findIntermediateStops(array $stops, int $from, int $to, array $stopRoadMap): array
    {
        $intermediates = [];
        $fromStop = $stops[$from];
        $toStop = $stops[$to];

        $totalDist = $this->haversine(
            $fromStop['lat'],
            $fromStop['lng'],
            $toStop['lat'],
            $toStop['lng']
        );

        $roadsA = $stopRoadMap[$fromStop['id']] ?? [];
        $roadsB = $stopRoadMap[$toStop['id']] ?? [];
        $relevantRoads = array_unique(array_merge($roadsA, $roadsB));

        foreach ($stops as $i => $stop) {
            if ($i === $from || $i === $to)
                continue;

            // skip if too close to FROM or TO (within 30m) — prevents endpoint duplication
            $distFromFrom = $this->haversine($fromStop['lat'], $fromStop['lng'], $stop['lat'], $stop['lng']);
            $distFromTo = $this->haversine($toStop['lat'], $toStop['lng'], $stop['lat'], $stop['lng']);

            if ($distFromFrom < 30 || $distFromTo < 30)
                continue;

            // must be on a shared road
            $stopRoads = $stopRoadMap[$stop['id']] ?? [];
            $shared = array_intersect($stopRoads, $relevantRoads);
            if (empty($shared))
                continue;

            // strict between check — sum of distances must not exceed total by more than 15%
            if (($distFromFrom + $distFromTo) > ($totalDist * 1.15))
                continue;

            // must be strictly between from and to — not past either end
            if ($distFromFrom >= $totalDist)
                continue;
            if ($distFromTo >= $totalDist)
                continue;

            $intermediates[] = array_merge($stop, [
                '_dist_from_start' => $distFromFrom
            ]);
        }

        // sort strictly forward — closest to FROM first
        usort($intermediates, fn($a, $b) => $a['_dist_from_start'] <=> $b['_dist_from_start']);

        // remove helper key
        return array_map(function ($s) {
            unset($s['_dist_from_start']);
            return $s;
        }, $intermediates);
    }
}