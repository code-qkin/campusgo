<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarpoolRide;
use App\Models\CarpoolPassenger;

class DriverController extends Controller
{
    public function accept(Request $request, $id)
    {
        $ride = CarpoolRide::findOrFail($id);
        if ($ride->status !== 'available') {
            return response()->json(['message' => 'ride is not available'], 400);
        }
        if ($request->user()->role !== 'driver') {
            return response()->json(['message' => 'unauthorized'], 403);
        }
        $ride->update([
            'driver_id' => $request->user()->id,
            'status' => 'accepted',
        ]);
        return response()->json($ride);
    }

    public function start(Request $request, $id)
    {
        $ride = CarpoolRide::findOrFail($id);
        if ($ride->status !== 'accepted') {
            return response()->json(['message' => 'ride is not accepted'], 400);
        }

        if ($ride->driver_id !== $request->user()->id) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $ride->update([
            'status' => 'ongoing'
        ]);
        return response()->json($ride);
    }
    public function complete(Request $request, $id)
    {
        $ride = CarpoolRide::findOrFail($id);
        if ($ride->status !== 'ongoing') {
            return response()->json(['message' => 'ride is not ongoing'], 400);
        }
        if ($ride->driver_id !== $request->user()->id) {
            return response()->json(['message' => 'unauthorized'], 403);
        }
        $ride->update([
            'status' => 'completed'
        ]);
        CarpoolPassenger::where('ride_id', $ride->id)->whereIn('status', ['waiting', 'confirmed', 'onboard'])->update(['status' => 'completed']);
        return response()->json($ride);
    }
    public function available(Request $request)
    {
        $rides = CarpoolRide::with(['passengers', 'route', 'route.stops'])
            ->where('campus_id', $request->user()->campus_id)
            ->where('status', 'available')
            ->latest()
            ->get();

        return response()->json($rides);
    }

    public function myRide(Request $request)
    {
        $ride = CarpoolRide::with(['passengers', 'route', 'route.stops'])
            ->where('driver_id', $request->user()->id)
            ->whereIn('status', ['accepted', 'ongoing'])
            ->first();

        return response()->json($ride);
    }

    public function history(Request $request)
    {
        $rides = CarpoolRide::with(['passengers', 'route'])
            ->where('driver_id', $request->user()->id)
            ->where('status', 'completed')
            ->latest()
            ->get();

        return response()->json($rides);
    }
    public function completePassenger(Request $request, $rideId, $passengerId)
    {
        $ride = CarpoolRide::findOrFail($rideId);

        if ($ride->status !== 'ongoing') {
            return response()->json(['message' => 'Ride is not ongoing'], 400);
        }

        if ($ride->driver_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $passenger = CarpoolPassenger::where('id', $passengerId)
            ->where('ride_id', $rideId)
            ->firstOrFail();

        if ($passenger->status === 'completed') {
            return response()->json(['message' => 'Passenger already completed'], 400);
        }

        $passenger->update(['status' => 'completed']);
        $ride->increment('seats_available');
        $ride = CarpoolRide::with(['passengers', 'route', 'route.stops'])
        ->findOrFail($rideId);


        // check if all active passengers are done
        $remainingPassengers = CarpoolPassenger::where('ride_id', $rideId)
            ->whereIn('status', ['waiting', 'confirmed', 'onboard'])
            ->count();

        if ($remainingPassengers === 0) {
            $ride->update(['status' => 'completed']);
        }

        return response()->json([
            'passenger' => $passenger,
            'ride' => $ride,
        ]);
    }

    public function acceptPassenger(Request $request, $rideId, $passengerId)
    {
        $ride = CarpoolRide::findOrFail($rideId);

        if ($ride->driver_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $passenger = CarpoolPassenger::where('id', $passengerId)
            ->where('ride_id', $rideId)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($ride->seats_available <= 0) {
            return response()->json([
                'message' => 'No seats available yet. Wait for a passenger to exit first.'
            ], 400);
        }

        $passenger->update(['status' => 'waiting']);
        $ride->decrement('seats_available');

        // reload with full relationships so frontend doesn't go blank
        $ride = CarpoolRide::with(['passengers', 'route', 'route.stops'])
            ->findOrFail($rideId);

        return response()->json(['passenger' => $passenger, 'ride' => $ride]);
    }

    public function rejectPassenger(Request $request, $rideId, $passengerId)
    {
        $ride = CarpoolRide::findOrFail($rideId);

        if ($ride->driver_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $passenger = CarpoolPassenger::where('id', $passengerId)
            ->where('ride_id', $rideId)
            ->where('status', 'pending')
            ->firstOrFail();

        $passenger->update(['status' => 'rejected']);

        return response()->json(['message' => 'Passenger rejected']);
    }
}
