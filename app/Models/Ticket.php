<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Ticket extends Pivot
{
    use HasFactory;

    public function code(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value - 1,
            get: fn ($value) => $value + 1
        );
    }
}
