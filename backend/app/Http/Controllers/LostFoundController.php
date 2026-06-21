<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LostFoundItem;

class LostFoundController extends Controller
{
    public function index(Request $request)
    {
        $campusId = $request->user()->campus_id;

        $items = LostFoundItem::with('reporter')
            ->where('campus_id', $campusId)
            ->latest()
            ->get();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'category'      => 'required|string',
            'location'      => 'required|string',
            'description'   => 'nullable|string',
            'contact_phone' => 'required|string|max:20',
            'image'         => 'nullable|image|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = uniqid() . '_' . time() . '.jpg';
            $destination = public_path('uploads/lost-found');

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $filename);
            $imageUrl = url('uploads/lost-found/' . $filename);
        }

        $item = LostFoundItem::create([
            'campus_id'     => $request->user()->campus_id,
            'reporter_id'   => $request->user()->id,
            'name'          => $request->name,
            'category'      => $request->category,
            'location'      => $request->location,
            'description'   => $request->description,
            'contact_phone' => $request->contact_phone,
            'image_url'     => $imageUrl,
            'is_claimed'    => false,
        ]);

        return response()->json($item, 201);
    }

    public function update(Request $request, $id)
    {
        $item = LostFoundItem::findOrFail($id);

        if ($item->reporter_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name'          => 'sometimes|string|max:255',
            'category'      => 'sometimes|string',
            'location'      => 'sometimes|string',
            'description'   => 'nullable|string',
            'contact_phone' => 'sometimes|string|max:20',
        ]);

        $item->update($request->only(['name', 'category', 'location', 'description', 'contact_phone']));

        return response()->json($item);
    }

    public function destroy(Request $request, $id)
    {
        $item = LostFoundItem::findOrFail($id);

        if ($item->reporter_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted']);
    }

    // PATCH /api/lost-found/{id}/mark-claimed — reporter marks it as handed over
    public function markClaimed(Request $request, $id)
    {
        $item = LostFoundItem::findOrFail($id);

        if ($item->reporter_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item->update(['is_claimed' => true]);

        return response()->json($item);
    }
}