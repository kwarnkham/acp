<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasFilter;

    protected $guarded = ['id'];

    protected $hidden = ['password'];

    protected $appends = ['is_admin'];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function isAdmin(): Attribute
    {
        return Attribute::get(fn () => $this->roles()->where('name', 'admin')->exists());
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains(fn ($role) => $role->name == $roleName);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function generateToken()
    {
        $this->tokens()->delete();
        $token = $this->createToken('fb');
        return $token->plainTextToken;
    }
}
