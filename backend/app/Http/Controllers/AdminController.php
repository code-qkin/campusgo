<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CarpoolRide;
use App\Models\DriverProfile;
use App\Models\LostFoundItem;

class AdminController extends Controller
{
    public function stats(Request $request)
    {
        $campusId = $request->user()->campus_id;
        $isSuperAdmin = $request->user()->role === 'super_admin';

        $studentsQuery = User::where('role', 'student');
        $ridesQuery = CarpoolRide::query();
        $lostFoundQuery = LostFoundItem::where('is_claimed', false);

        if (!$isSuperAdmin) {
            $studentsQuery->where('campus_id', $campusId);
            $ridesQuery->where('campus_id', $campusId);
            $lostFoundQuery->where('campus_id', $campusId);
        }

        return response()->json([
            'total_students' => $studentsQuery->count(),
            'active_rides' => (clone $ridesQuery)->whereIn('status', ['filling', 'available', 'accepted', 'ongoing'])->count(),
            'completed_rides' => (clone $ridesQuery)->where('status', 'completed')->count(),
            'lost_found_open' => $lostFoundQuery->count(),
            'carpools_today' => (clone $ridesQuery)->whereDate('created_at', today())->count(),
        ]);
    }

    public function students(Request $request)
    {
        $campusId = $request->user()->campus_id;
        $isSuperAdmin = $request->user()->role === 'super_admin';

        $query = User::where('role', 'student')->with('campus');

        if (!$isSuperAdmin) {
            $query->where('campus_id', $campusId);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return response()->json($query->latest()->get());
    }

    public function toggleStudent(Request $request, $id)
    {
        $student = User::findOrFail($id);
        $student->update(['is_active' => !$student->is_active]);
        return response()->json($student);
    }

    public function drivers(Request $request)
    {
        $campusId     = $request->user()->campus_id;
        $isSuperAdmin = $request->user()->role === 'super_admin';

        $query = User::where('role', 'driver')->with(['driverProfile', 'campus']);

        if (!$isSuperAdmin) {
            $query->where('campus_id', $campusId);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return response()->json($query->latest()->get());
    }

    public function approveDriver(Request $request, $id)
    {
        $profile = DriverProfile::where('user_id', $id)->firstOrFail();
        $profile->update([
            'status'      => 'approved',
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);
        return response()->json(['message' => 'Driver approved.']);
    }

    public function rejectDriver(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $profile = DriverProfile::where('user_id', $id)->firstOrFail();
        $profile->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);
        return response()->json(['message' => 'Driver rejected.']);
    }
}