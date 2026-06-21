<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampusStop extends Model
{
    protected $fillable = [
        'campus_id', 'name', 'lat', 'lng', 'is_popular'
    ];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}