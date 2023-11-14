<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasFilter
{
    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when(
            $filters['name'] ?? null,
            fn (Builder $query, $name) => $query->where('name', 'like', '%' . $name . '%')
        );

        $query->when(
            $filters['on_salse'] ?? null,
            fn ($query) => $query->whereNull('ticket_id')
        );

        $query->when(
            $filters['sold'] ?? null,
            fn ($query) => $query->whereNotNull('ticket_id')
        );

        $query->when(
            $filters['item_id'] ?? null,
            fn ($query, $itemId) =>
            $query->where('item_id', $itemId)
        );

        $query->when(
            $filters['status'] ?? null,
            fn ($query, $status) =>
            $query->whereIn('status', explode(',', $status))
        );

        $query->when(
            $filters['select'] ?? null,
            fn (Builder $query, $select) =>  $query->select($select)
        );

        $query->when(
            $filters['round_id'] ?? null,
            fn ($query, $roundId) =>
            $query->where('round_id', $roundId)
        );

        $query->when(
            $filters['user_id'] ?? null,
            fn ($query, $userId) =>
            $query->where('user_id', $userId)
        );
    }
}
