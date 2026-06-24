<?php

namespace App\Http\Controllers;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class DriverAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'full_name'     => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => ['required', 'confirmed', PasswordRule::min(8)],
            'campus_id'     => 'required|exists:campuses,id',
            'vehicle_type'  => 'required|in:keke,car',
            'vehicle_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $path     = $request->file('vehicle_image')->store('driver-vehicles', 'public');
        $imageUrl = asset('storage/' . $path);

        $user = User::create([
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'campus_id' => $request->campus_id,
            'role'      => 'driver',
            'is_active' => true,
        ]);

        DriverProfile::create([
            'user_id'           => $user->id,
            'vehicle_type'      => $request->vehicle_type,
            'vehicle_image_url' => $imageUrl,
            'status'            => 'pending',
        ]);

        $token = $user->createToken('driver_token')->plainTextToken;

        return response()->json([
            'message'       => 'Registration submitted. Awaiting admin verification.',
            'user'          => $user,
            'token'         => $token,
            'driver_status' => 'pending',
        ], 201);
    }
}
