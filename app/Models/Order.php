<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public function rounds()
    {
        return $this->belongsToMany(Round::class)->withTimestamps()->withPivot(['code', 'price']);
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
