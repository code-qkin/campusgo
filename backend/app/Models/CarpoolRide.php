<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Campus;
use App\Models\Route;
use App\Models\User;
use App\Models\CarpoolPassenger;

class CarpoolRide extends Model
{
    protected $fillable = [
        'campus_id',
        'route_id',
        'creator_id',
        'driver_id',
        'vehicle_type',
        'seats_total',
        'seats_available',
        'ride_type',
        'status',
        'departure_time',
    ];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function passengers()
    {
        return $this->hasMany(CarpoolPassenger::class, 'ride_id');
    }
}
