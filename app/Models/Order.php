<?php

namespace App\Models;

use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    use HasFactory, HasFilter;

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public function tickets()
    {
        return $this->belongsToMany(Round::class)
            ->using(Ticket::class)
            ->withTimestamps()
            ->withPivot(['code', 'price', 'id']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    protected function screenshot(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Storage::url($value) : null,
        );
    }
}
