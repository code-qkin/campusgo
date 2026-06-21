<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionPoint extends Model
{
    protected $fillable = ['campus_id', 'name', 'lat', 'lng', 'is_active'];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}   