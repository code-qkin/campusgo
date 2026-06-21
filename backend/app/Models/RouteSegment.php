<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Route;
use App\Models\RouteStop;

class RouteSegment extends Model
{
    protected $fillable = [
        'route_id',
        'from_stop_id',
        'to_stop_id',
        'price',
    ];

    public function route()
    {
        // belongsTo
        return $this->belongsTo(Route::class);
    }

    public function toStop()
    {
        return $this->belongsTo(RouteStop::class, 'to_stop_id'); 
    }

    public function fromStop()
    {
        return $this->belongsTo(RouteStop::class, 'from_stop_id'); 
    }
}
