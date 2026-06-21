<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CampusStop;

class CampusStopController extends Controller
{
    public function index(Request $request)
    {
        $query = CampusStop::orderBy('name');

        if ($request->user()->role !== 'super_admin') {
            $query->where('campus_id', $request->user()->campus_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'lat'       => 'required|numeric',
            'lng'       => 'required|numeric',
            'campus_id' => 'nullable|integer|exists:campuses,id',
        ]);

        $campusId = $request->user()->role === 'super_admin'
            ? ($request->campus_id ?? $request->user()->campus_id)
            : $request->user()->campus_id;

        if (!$campusId) {
            return response()->json(['message' => 'Campus not found for this user'], 400);
        }

        $stop = CampusStop::create([
            'campus_id'  => $campusId,
            'name'       => $request->name,
            'lat'        => $request->lat,
            'lng'        => $request->lng,
            'is_popular' => false,
        ]);

        return response()->json($stop, 201);
    }

    public function update(Request $request, $id)
    {
        $stop = CampusStop::findOrFail($id);
        $request->validate(['name' => 'required|string|max:255']);
        $stop->update(['name' => $request->name]);
        return response()->json($stop);
    }

    public function destroy(Request $request, $id)
    {
        $stop = CampusStop::findOrFail($id);
        $stop->delete();
        return response()->json(['message' => 'Stop deleted']);
    }

    public function togglePopular(Request $request, $id)
    {
        $stop = CampusStop::findOrFail($id);
        $stop->update(['is_popular' => !$stop->is_popular]);
        return response()->json($stop);
    }
}