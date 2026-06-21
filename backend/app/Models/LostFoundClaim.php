<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LostFoundClaim extends Model
{
    protected $fillable = ['item_id', 'claimant_id', 'description', 'status'];

    public function item()
    {
        return $this->belongsTo(LostFoundItem::class, 'item_id');
    }

    public function claimant()
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }
}