<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\LostFoundController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RouteGeneratorController;
use App\Http\Controllers\CampusStopController;
use App\Http\Controllers\SocialAuthController;

// Public
Route::get('/campuses', [CampusController::class, 'index']);

// Auth — rate limited
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});
// Google OAuth — complete signup (called from frontend after campus selection)
Route::post('/auth/google/complete', [SocialAuthController::class, 'completeGoogleSignup'])
    ->middleware('throttle:10,1');

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);
});

// Authenticated
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout',                   [AuthController::class, 'logout']);
    Route::get('/me',                        [AuthController::class, 'me']);
    Route::patch('/user/profile',            [AuthController::class, 'updateProfile']);
    Route::patch('/user/password',           [AuthController::class, 'changePassword']);
    Route::post('/email/resend',             [AuthController::class, 'resendVerification']);

    // Stops
    Route::get('/stops/popular',             [RouteController::class, 'popularStops']);
    Route::patch('/stops/{id}/popular',      [RouteController::class, 'togglePopular']);
    Route::get('/stops',                     [RouteController::class, 'allStops']);

    // Routes (admin)
    Route::get('/routes',                    [RouteController::class, 'index']);
    Route::post('/routes',                   [RouteController::class, 'store']);
    Route::delete('/routes/{id}',            [RouteController::class, 'destroy']);
    Route::patch('/routes/{id}',             [RouteController::class, 'update']);
    Route::patch('/routes/{id}/toggle',      [RouteController::class, 'toggle']);

    // Rides — static routes BEFORE {id} wildcards
    Route::get('/rides/search',              [RideController::class, 'search']);
    Route::get('/rides/my',                  [RideController::class, 'myRides']);
    Route::get('/rides/history',             [RideController::class, 'history']);
    Route::get('/rides',                     [RideController::class, 'index']);
    Route::post('/rides',                    [RideController::class, 'store']);
    Route::post('/rides/{id}/join',          [RideController::class, 'join']);
    Route::post('/rides/{id}/leave',         [RideController::class, 'leave']);
    Route::delete('/rides/{id}',             [RideController::class, 'destroy']);

    // Driver
    Route::post('/rides/{id}/accept',                                    [DriverController::class, 'accept']);
    Route::post('/rides/{id}/start',                                     [DriverController::class, 'start']);
    Route::post('/rides/{id}/complete',                                  [DriverController::class, 'complete']);
    Route::patch('/rides/{rideId}/passengers/{passengerId}/complete',    [DriverController::class, 'completePassenger']);
    Route::patch('/rides/{rideId}/passengers/{passengerId}/accept',      [DriverController::class, 'acceptPassenger']);
    Route::patch('/rides/{rideId}/passengers/{passengerId}/reject',      [DriverController::class, 'rejectPassenger']);
    Route::get('/driver/available',                                      [DriverController::class, 'available']);
    Route::get('/driver/my-ride',                                        [DriverController::class, 'myRide']);
    Route::get('/driver/history',                                        [DriverController::class, 'history']);

    // Lost & Found
    Route::get('/lost-found',                [LostFoundController::class, 'index']);
    Route::post('/lost-found',               [LostFoundController::class, 'store']);
    Route::patch('/lost-found/{id}',         [LostFoundController::class, 'update']);
    Route::delete('/lost-found/{id}',        [LostFoundController::class, 'destroy']);
    Route::patch('/lost-found/{id}/mark-claimed', [LostFoundController::class, 'markClaimed']);

    // Admin
    Route::post('/campuses',                 [CampusController::class, 'store']);
    Route::get('/admin/stats',               [AdminController::class, 'stats']);
    Route::get('/admin/students',            [AdminController::class, 'students']);
    Route::patch('/admin/students/{id}/toggle', [AdminController::class, 'toggleStudent']);

    // Campus stops
    Route::get('/campus-stops',              [CampusStopController::class, 'index']);
    Route::post('/campus-stops',             [CampusStopController::class, 'store']);
    Route::patch('/campus-stops/{id}',       [CampusStopController::class, 'update']);
    Route::delete('/campus-stops/{id}',      [CampusStopController::class, 'destroy']);
    Route::patch('/campus-stops/{id}/popular', [CampusStopController::class, 'togglePopular']);

    // Route generation
    Route::post('/admin/generate-routes-from-stops', [RouteGeneratorController::class, 'generateFromStops']);
    Route::post('/admin/generate-routes',             [RouteGeneratorController::class, 'generate']);
});
