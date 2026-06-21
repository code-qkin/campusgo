<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\CarpoolPassenger;
use App\Models\CarpoolRide;
use App\Models\RouteSegment;

class RouteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Route::with('stops', 'campus');

        if ($user->role !== 'super_admin') {
            $query->where('campus_id', $user->campus_id);
        }

        return response()->json($query->get());
    }
    public function allStops(Request $request)
    {
        $stops = RouteStop::whereHas('route', function ($q) use ($request) {
            $q->where('campus_id', $request->user()->campus_id)
                ->where('is_active', true);
        })->with('route')->orderBy('route_id')->orderBy('order')->get();

        return response()->json($stops);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stops' => 'required|array|min:2',
            'stops.*.name' => 'required|string',
            'stops.*.lat' => 'required|numeric',
            'stops.*.lng' => 'required|numeric',
            'stops.*.order' => 'required|integer',
        ]);

        $route = Route::findOrFail($id);
        $route->update(['name' => $request->name]);

        $stopIds = $route->stops()->pluck('id')->toArray();

        if (!empty($stopIds)) {
            CarpoolPassenger::whereIn('boarding_stop_id', $stopIds)
                ->orWhereIn('exit_stop_id', $stopIds)
                ->delete();
        }

        // clean up dependent records before deleting stops
        RouteSegment::where('route_id', $id)->delete();
        $route->stops()->delete();

        foreach ($request->stops as $stop) {
            RouteStop::create([
                'route_id' => $route->id,
                'name' => $stop['name'],
                'order' => $stop['order'],
                'lat' => $stop['lat'],
                'lng' => $stop['lng'],
            ]);
        }

        return response()->json($route->load('stops'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stops' => 'required|array|min:2',
            'stops.*.name' => 'required|string',
            'stops.*.lat' => 'required|numeric',
            'stops.*.lng' => 'required|numeric',
            'stops.*.order' => 'required|integer',
            'campus_id' => 'nullable|integer|exists:campuses,id',
        ]);

        $campusId = $request->user()->role === 'super_admin'
            ? $request->campus_id
            : $request->user()->campus_id;

        if (!$campusId) {
            return response()->json(['message' => 'Please select a campus'], 400);
        }

        $route = Route::create([
            'campus_id' => $campusId,
            'name' => $request->name,
            'is_active' => true,
        ]);

        foreach ($request->stops as $stop) {
            RouteStop::create([
                'route_id' => $route->id,
                'name' => $stop['name'],
                'order' => $stop['order'],
                'lat' => $stop['lat'],
                'lng' => $stop['lng'],
            ]);
        }

        return response()->json($route->load('stops'), 201);
    }





    public function toggle(Request $request, $id)
    {
        $route = Route::findOrFail($id);
        $route->update(['is_active' => !$route->is_active]);
        return response()->json($route);
    }

    public function destroy(Request $request, $id)
    {
        $route = Route::findOrFail($id);

        // get all stop IDs for this route
        $stopIds = $route->stops()->pluck('id')->toArray();

        // delete passengers referencing these stops
        if (!empty($stopIds)) {
            CarpoolPassenger::whereIn('boarding_stop_id', $stopIds)
                ->orWhereIn('exit_stop_id', $stopIds)
                ->delete();
        }

        // delete passengers on rides for this route
        CarpoolPassenger::whereHas('ride', function ($q) use ($id) {
            $q->where('route_id', $id);
        })->delete();

        // delete rides
        CarpoolRide::where('route_id', $id)->delete();

        // delete segments
        RouteSegment::where('route_id', $id)->delete();

        // delete stops
        $route->stops()->delete();

        // delete route
        $route->delete();

        return response()->json(['message' => 'Route deleted']);
    }

    public function popularStops(Request $request)
    {
        $stops = RouteStop::whereHas('route', function ($q) use ($request) {
            $q->where('campus_id', $request->user()->campus_id)
                ->where('is_active', true);
        })
            ->where('is_popular', true)
            ->with('route')
            ->get();

        return response()->json($stops);
    }

    public function togglePopular(Request $request, $id)
    {
        $stop = RouteStop::findOrFail($id);
        $stop->update(['is_popular' => !$stop->is_popular]);
        return response()->json($stop);
    }
}
