<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $frontend = rtrim(config('app.frontend_url'), '/');

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return redirect($frontend . '/auth/callback?oauth_error=failed');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Existing user — log them straight in
            if ($user->is_active === false) {
                return redirect($frontend . '/auth/callback?oauth_error=suspended');
            }

            // Mark email verified if not already (Google guarantees it)
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return redirect(
                $frontend . '/auth/callback'
                . '?oauth_token=' . urlencode($token)
                . '&oauth_user='  . urlencode(json_encode($user->fresh()))
            );
        }

        // New user — send to frontend to pick their campus
        return redirect(
            $frontend . '/auth/callback'
            . '?oauth_pending=1'
            . '&oauth_name='  . urlencode($googleUser->getName())
            . '&oauth_email=' . urlencode($googleUser->getEmail())
        );
    }

    public function completeGoogleSignup(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'campus_id' => 'required|exists:campuses,id',
        ]);

        $user = User::create([
            'full_name'         => $request->full_name,
            'email'             => $request->email,
            'password'          => Hash::make(Str::random(32)),
            'campus_id'         => $request->campus_id,
            'email_verified_at' => now(), // Google already verified the email
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }
}
