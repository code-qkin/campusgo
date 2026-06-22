<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Google OAuth
Route::get('/auth/google/redirect',  [SocialAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback',  [SocialAuthController::class, 'handleGoogleCallback']);

// Email verification link — signed URL, redirects to frontend after verifying
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');
