<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Campus;
use App\Models\RouteStop;
use App\Models\RouteSegment;

class Route extends Model
{
    protected $fillable = [
        'campus_id',
        'name',
        'is_active',
    ];

    public function campus()
    {
        // belongsTo
        return $this->belongsTo(Campus::class);
    }

    public function stops()
    {
        // hasMany
        return $this->hasMany(RouteStop::class);
    }

    public function segments()
    {
        // hasMany
        return $this->hasMany(RouteSegment::class);
    }
}