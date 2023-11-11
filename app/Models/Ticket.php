<?php

namespace App\Models;

use App\Traits\HasFilter;

class Ticket extends BaseModel
{
    use HasFilter;

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot(['expires_at', 'screenshot', 'id']);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id', 'id', 'buyer');
    }
}
