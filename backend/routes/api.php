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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/campuses', [CampusController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    // stops
    Route::get('/stops/popular', [RouteController::class, 'popularStops']);
    Route::patch('/stops/{id}/popular', [RouteController::class, 'togglePopular']);
    Route::get('/stops', [RouteController::class, 'allStops']);

    // routes
    Route::get('/routes', [RouteController::class, 'index']);
    Route::post('/routes', [RouteController::class, 'store']);
    Route::delete('/routes/{id}', [RouteController::class, 'destroy']);
    Route::patch('/routes/{id}', [RouteController::class, 'update']);

    // rides — static routes FIRST before {id} routes
    Route::get('/rides/search', [RideController::class, 'search']);
    Route::get('/rides/my', [RideController::class, 'myRides']);
    Route::get('/rides', [RideController::class, 'index']);
    Route::post('/rides', [RideController::class, 'store']);

    //history
    Route::get('/rides/history', [RideController::class, 'history']);

    // admin endpoints
    Route::get('/admin/stats', [AdminController::class, 'stats']);
    Route::get('/admin/students', [AdminController::class, 'students']);
    Route::patch('/admin/students/{id}/toggle', [AdminController::class, 'toggleStudent']);
    Route::patch('/routes/{id}/toggle', [RouteController::class, 'toggle']);

    // rides with ID
    Route::post('/rides/{id}/join', [RideController::class, 'join']);
    Route::post('/rides/{id}/leave', [RideController::class, 'leave']);
    Route::delete('/rides/{id}', [RideController::class, 'destroy']);
    Route::post('/rides/{id}/accept', [DriverController::class, 'accept']);
    Route::post('/rides/{id}/start', [DriverController::class, 'start']);
    Route::post('/rides/{id}/complete', [DriverController::class, 'complete']);
    Route::patch('/rides/{rideId}/passengers/{passengerId}/complete', [DriverController::class, 'completePassenger']);

    // driver
    Route::get('/driver/available', [DriverController::class, 'available']);
    Route::get('/driver/my-ride', [DriverController::class, 'myRide']);
    Route::get('/driver/history', [DriverController::class, 'history']);
    Route::patch('/rides/{rideId}/passengers/{passengerId}/accept', [DriverController::class, 'acceptPassenger']);
    Route::patch('/rides/{rideId}/passengers/{passengerId}/reject', [DriverController::class, 'rejectPassenger']);


    // lost & found
    Route::get('/lost-found', [LostFoundController::class, 'index']);
    Route::post('/lost-found', [LostFoundController::class, 'store']);
    Route::patch('/lost-found/{id}', [LostFoundController::class, 'update']);
    Route::delete('/lost-found/{id}', [LostFoundController::class, 'destroy']);
    Route::patch('/lost-found/{id}/mark-claimed', [LostFoundController::class, 'markClaimed']);

    //campus
    Route::post('/campuses', [CampusController::class, 'store']);



    // campus stops
    Route::get('/campus-stops', [CampusStopController::class, 'index']);
    Route::post('/campus-stops', [CampusStopController::class, 'store']);
    Route::patch('/campus-stops/{id}', [CampusStopController::class, 'update']);
    Route::delete('/campus-stops/{id}', [CampusStopController::class, 'destroy']);
    Route::patch('/campus-stops/{id}/popular', [CampusStopController::class, 'togglePopular']);
    Route::post('/admin/generate-routes-from-stops', [RouteGeneratorController::class, 'generateFromStops']);
    Route::post('/admin/generate-routes', [RouteGeneratorController::class, 'generate']);



});

