<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Route;

class Campus extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'email_domain',
        'logo_url',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function routes()
    {
        return $this->hasMany(Route::class);
    }
}
