<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable // implements MustVerifyEmail — re-enable when domain is verified on resend.com
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'campus_id',
        'full_name',
        'email',
        'avatar_url',
        'role',
        'password',
        'points',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class);
    }
}
