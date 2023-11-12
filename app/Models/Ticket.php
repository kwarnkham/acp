<?php

namespace App\Models;

use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class Ticket extends BaseModel
{
    use HasFilter;

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot([
            'expires_at',
            'screenshot',
            'id',
            'phone',
            'phone',
            'price'
        ])->withTimestamps();
    }

    public function currentUser(): Attribute
    {
        return Attribute::make(
            get: function () {
                $currentUser = $this->users()->wherePivot('expires_at', '>', now())->first();
                $currentUser->pivot->expires_at = new Carbon($currentUser->pivot->expires_at);
                return $currentUser;
            }
        );
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id', 'id', 'buyer');
    }
}
