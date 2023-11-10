<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasFilter
{
    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when(
            $filters['name'] ?? null,
            fn (Builder $query, $name) => $query->where(function (Builder $query) use ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            })
        );
    }
}
