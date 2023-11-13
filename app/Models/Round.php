<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    protected $guarded = [''];

    public function orderDetails()
    {
        return $this->belongsToMany(Order::class)->withTimestamps()->withPivot(['code', 'price']);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
