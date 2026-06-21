<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Route;
use App\Models\RouteSegment;

class RouteStop extends Model
{
    protected $fillable = [
        'route_id',
        'name',
        'order',
        'lat',
        'lng',
        'is_popular',
    ];

    public function route()
    {
        // belongsTo
        return $this->belongsTo(Route::class);
    }   

    public function segmentsFrom()
{
    return $this->hasMany(RouteSegment::class, 'from_stop_id');
    // second argument tells Laravel which foreign key to use
    // because it's not the default 'route_stop_id'
}
}
