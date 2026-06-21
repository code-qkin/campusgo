<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // 1. fillable — list all columns from your users migration
    //    except id, email_verified_at, remember_token, created_at, updated_at
    protected $fillable = [
        'campus_id',
        'full_name',
        'email',
        'role',
        'password',
        'points'    
    ];

    // 2. hidden — these never get sent in API responses
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 3. casts — tell Laravel how to treat certain columns
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 4. relationship — a user belongs to a campus
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}