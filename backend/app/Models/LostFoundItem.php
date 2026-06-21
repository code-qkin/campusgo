<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LostFoundItem extends Model
{
    protected $fillable = [
        'campus_id',
        'reporter_id',
        'name',
        'category',
        'location',
        'description',
        'image_url',
        'contact_phone',
        'is_claimed',
        'claimed_by',
        'collection_point_id',
        'claim_approved',
    ];

    public function reporter()
    {
       return $this->belongsTo(User::class, 'reporter_id');
    }

    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}
