<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Ticket extends BaseModel
{
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

    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when($filters['item_id'] ?? null, function ($query, $itemId) {
            $query->where('item_id', $itemId);
        });

        $query->when($filters['status'] ?? null, function ($query, $status) {
            $query->whereIn('status', explode(',', $status));
        });
    }
}
