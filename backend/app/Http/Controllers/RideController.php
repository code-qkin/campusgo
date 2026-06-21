<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarpoolRide;
use App\Models\CarpoolPassenger;
use App\Models\RouteSegment;
use App\Models\RouteStop;

class RideController extends Controller
{
    public function index(Request $request)
    {
        $campusId = $request->user()->campus_id;
        $rides = CarpoolRide::with(['passengers', 'route', 'route.stops'])->where('campus_id', $campusId)->whereIn('status', ['filling', 'available', 'ongoing', 'accepted'])->get();
        return response()->json($rides);
    }


    public function search(Request $request)
    {
        $request->validate([
            'boarding_stop_id' => 'required|exists:route_stops,id',
            'exit_stop_id' => 'required|exists:route_stops,id',
        ]);

        $boardingStop = \App\Models\RouteStop::findOrFail($request->boarding_stop_id);
        $exitStop = \App\Models\RouteStop::findOrFail($request->exit_stop_id);

        if ($boardingStop->route_id !== $exitStop->route_id) {
            return response()->json(['message' => 'Stops must be on the same route'], 400);
        }

        if ($boardingStop->order >= $exitStop->order) {
            return response()->json(['message' => 'Exit stop must be after boarding stop'], 400);
        }

        $rides = CarpoolRide::with(['passengers', 'route', 'route.stops'])
            ->where('campus_id', $request->user()->campus_id)
            ->whereIn('status', ['filling', 'ongoing'])
            ->where('route_id', $boardingStop->route_id)
            ->get()
            ->filter(function ($ride) use ($boardingStop) {
                $occupiedSeats = \App\Models\CarpoolPassenger::where('ride_id', $ride->id)
                    ->where('boarding_stop_id', '<=', $boardingStop->id)
                    ->where('exit_stop_id', '>', $boardingStop->id)
                    ->whereIn('status', ['waiting', 'confirmed', 'onboard'])
                    ->count();
                return $occupiedSeats < $ride->seats_total;
            })
            ->values();

        return response()->json($rides);
    }
    public function store(Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'vehicle_type' => 'required|string',
            'ride_type' => 'required|string',
            'departure_time' => 'nullable|date',
        ]);

        $seatsTotal = $request->vehicle_type === 'car' ? 4 : 3;
        $status = $request->ride_type === 'alone' ? 'available' : 'filling';

        // check if user is already on an active ride
        $activeRide = CarpoolPassenger::where('student_id', $request->user()->id)
            ->whereIn('status', ['waiting', 'confirmed', 'onboard'])
            ->whereHas('ride', function ($q) use ($request) {
                $q->whereNotIn('status', ['completed', 'cancelled'])
                    ->where('creator_id', '!=', $request->user()->id);  // exclude own rides
            })
            ->exists();

        if ($activeRide) {
            return response()->json([
                'message' => 'You are already on a ride. Leave it before creating a new one.'
            ], 400);
        }

        $ride = CarpoolRide::create([
            'campus_id' => $request->user()->campus_id,
            'creator_id' => $request->user()->id,
            'route_id' => $request->route_id,
            'vehicle_type' => $request->vehicle_type,
            'ride_type' => $request->ride_type,
            'seats_total' => $seatsTotal,
            'seats_available' => $seatsTotal,
            'status' => $status,
            'departure_time' => $request->departure_time,
            'driver_id' => null,
        ]);
        $firstStop = RouteStop::where('route_id', $request->route_id)
            ->orderBy('order', 'asc')->first();
        $lastStop = RouteStop::where('route_id', $request->route_id)
            ->orderBy('order', 'desc')->first();

        if ($firstStop && $lastStop) {
            CarpoolPassenger::create([
                'ride_id' => $ride->id,
                'student_id' => $request->user()->id,
                'boarding_stop_id' => $firstStop->id,
                'exit_stop_id' => $lastStop->id,
                'fare' => 0,
                'join_type' => 'prebooked',
                'status' => 'waiting',
            ]);

            // decrease available seats by 1
            $ride->decrement('seats_available');
            $ride->refresh();
        }
        return response()->json($ride, 201);
    }

    public function myRides(Request $request)
    {
        $rides = CarpoolRide::with(['passengers', 'route', 'route.stops'])
            ->whereHas('passengers', function ($q) use ($request) {
                $q->where('student_id', $request->user()->id)
                    ->whereIn('status', ['waiting', 'confirmed', 'onboard']);
            })
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        return response()->json($rides);
    }

    public function join(Request $request, $id)
    {
        $ride = CarpoolRide::findOrFail($id);

        if (!in_array($ride->status, ['filling', 'ongoing'])) {
            return response()->json(['message' => 'Ride is not accepting passengers'], 400);
        }

        // validate first
        $request->validate([
            'boarding_stop_id' => 'required|exists:route_stops,id',
            'exit_stop_id' => 'required|exists:route_stops,id',
        ]);

        // check already on this ride
        $alreadyJoined = CarpoolPassenger::where('ride_id', $ride->id)
            ->where('student_id', $request->user()->id)
            ->whereIn('status', ['waiting', 'confirmed', 'onboard', 'pending'])
            ->exists();

        if ($alreadyJoined) {
            return response()->json(['message' => 'You have already joined this ride'], 400);
        }

        // check already on another active ride
        $activeRideIds = CarpoolPassenger::where('student_id', $request->user()->id)
            ->whereIn('status', ['waiting', 'confirmed', 'onboard'])
            ->pluck('ride_id')
            ->toArray();

        $onAnotherRide = CarpoolRide::whereIn('id', $activeRideIds)
            ->where('id', '!=', $ride->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($onAnotherRide) {
            return response()->json([
                'message' => 'You are already on another ride. Leave it before joining a new one.'
            ], 400);
        }

        // for ongoing rides — check stop-based request limit
        if ($ride->status === 'ongoing') {
            // how many active passengers are dropping off at this boarding stop
            $droppingOff = CarpoolPassenger::where('ride_id', $ride->id)
                ->where('exit_stop_id', $request->boarding_stop_id)
                ->whereIn('status', ['waiting', 'confirmed', 'onboard'])
                ->count();

            // how many pending requests already exist at this boarding stop
            $pendingAtStop = CarpoolPassenger::where('ride_id', $ride->id)
                ->where('boarding_stop_id', $request->boarding_stop_id)
                ->where('status', 'pending')
                ->count();

            if ($droppingOff === 0) {
                return response()->json([
                    'message' => 'No passengers are dropping off at this stop.'
                ], 400);
            }

            if ($pendingAtStop >= $droppingOff) {
                return response()->json([
                    'message' => 'All available spots at this stop are already requested.'
                ], 400);
            }
        }

        // check rolling seat availability (for filling rides)
        if ($ride->status === 'filling') {
            $occupiedSeats = CarpoolPassenger::where('ride_id', $ride->id)
                ->where('boarding_stop_id', '<=', $request->boarding_stop_id)
                ->where('exit_stop_id', '>', $request->boarding_stop_id)
                ->whereIn('status', ['waiting', 'confirmed', 'onboard'])
                ->count();

            if ($occupiedSeats >= $ride->seats_total) {
                return response()->json(['message' => 'No seats available on this segment'], 400);
            }
        }

        // calculate fare
        $fare = RouteSegment::where('route_id', $ride->route_id)
            ->where('from_stop_id', '>=', $request->boarding_stop_id)
            ->where('to_stop_id', '<=', $request->exit_stop_id)
            ->sum('price');

        // check for cancelled passenger — reactivate instead of creating new
        $cancelledPassenger = CarpoolPassenger::where('ride_id', $ride->id)
            ->where('student_id', $request->user()->id)
            ->where('status', 'cancelled')
            ->first();

        if ($cancelledPassenger) {
            $cancelledPassenger->update([
                'boarding_stop_id' => $request->boarding_stop_id,
                'exit_stop_id' => $request->exit_stop_id,
                'fare' => $fare,
                'status' => $ride->status === 'ongoing' ? 'pending' : 'waiting',
            ]);

            if ($ride->status === 'filling') {
                $ride->decrement('seats_available');
                $ride->refresh();
                if ($ride->seats_available === 0) {
                    $ride->update(['status' => 'available']);
                }
            }

            return response()->json($cancelledPassenger, 201);
        }

        // create new passenger
        $joinType = $ride->status === 'filling' ? 'prebooked' : 'midroute';
        $passengerStatus = $ride->status === 'ongoing' ? 'pending' : 'waiting';

        $passenger = CarpoolPassenger::create([
            'ride_id' => $ride->id,
            'student_id' => $request->user()->id,
            'boarding_stop_id' => $request->boarding_stop_id,
            'exit_stop_id' => $request->exit_stop_id,
            'fare' => $fare,
            'join_type' => $joinType,
            'status' => $passengerStatus,
        ]);

        if ($ride->status === 'filling') {
            $ride->decrement('seats_available');
            $ride->refresh();
            if ($ride->seats_available === 0 && $ride->status === 'filling') {
                $ride->update(['status' => 'available']);
            }
        }

        return response()->json($passenger, 201);
    }
    public function leave(Request $request, $id)
    {
        $ride = CarpoolRide::findOrFail($id);

        $passenger = CarpoolPassenger::where('ride_id', $ride->id)
            ->where('student_id', $request->user()->id)
            ->whereIn('status', ['waiting', 'confirmed', 'pending'])
            ->first();

        if (!$passenger) {
            return response()->json(['message' => 'You are not on this ride'], 400);
        }
        $passenger->update(['status' => 'cancelled']);

        // only increment seats if passenger was actively taking a seat
        if (in_array($passenger->status, ['waiting', 'confirmed'])) {
            $ride->increment('seats_available');
            $ride->refresh();
            if ($ride->status === 'available' && $ride->seats_available > 0) {
                $ride->update(['status' => 'filling']);
            }
        }

        return response()->json(['message' => 'Left ride successfully']);
    }
    public function destroy(Request $request, $id)
    {
        $ride = CarpoolRide::findOrFail($id);

        if ($ride->creator_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (in_array($ride->status, ['ongoing', 'completed'])) {
            return response()->json(['message' => 'Cannot delete an active or completed ride'], 400);
        }

        $ride->delete();

        return response()->json(['message' => 'Ride deleted']);
    }

    public function history(Request $request)
    {
        $rides = CarpoolRide::with(['route', 'route.stops'])
            ->whereHas('passengers', function ($q) use ($request) {
                $q->where('student_id', $request->user()->id)
                    ->where('status', 'completed');
            })
            ->where('status', 'completed')
            ->latest()
            ->get();

        return response()->json($rides);
    }
}
