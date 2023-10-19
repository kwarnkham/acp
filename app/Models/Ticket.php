<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Ticket extends BaseModel
{
    public function item()
    {
        return $this->belongsTo(Item::class);
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
