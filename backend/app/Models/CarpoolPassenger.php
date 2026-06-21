<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CarpoolRide;
use App\Models\User;
use App\Models\RouteStop;

class CarpoolPassenger extends Model
{
    protected $fillable = [
        'ride_id',
        'student_id',
        'boarding_stop_id',
        'exit_stop_id',
        'fare',
        'join_type',
        'status',
    ];

    public function ride()
    {
        return $this->belongsTo(CarpoolRide::class, 'ride_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function boardingStop()
    {
        return $this->belongsTo(RouteStop::class, 'boarding_stop_id');
    }

    public function exitStop()
    {
        return $this->belongsTo(RouteStop::class, 'exit_stop_id');
    }
}
