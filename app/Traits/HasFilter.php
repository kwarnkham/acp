<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait HasFilter
{
    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when(
            $filters['name'] ?? null,
            fn (Builder $query, $name) => $query->where('name', 'like', '%' . $name . '%')
        );

        $query->when(
            $filters['id'] ?? null,
            fn (Builder $query, $id) => $query->where('id', 'like', '%' . $id . '%')
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

        $query->when(
            $filters['phone'] ?? null,
            fn ($query, $phone) =>
            $query->where('phone', 'like', "%$phone%")
        );

        $query->when(
            $filters['from'] ?? null,
            fn ($query, $from) =>
            $query->where('updated_at', '>=', new Carbon($from))
        );

        $query->when(
            $filters['to'] ?? null,
            fn ($query, $to) =>
            $query->where('updated_at', '<=', (new Carbon($to))->addDay()->subSecond())
        );
    }
}
