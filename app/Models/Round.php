<?php

namespace App\Models;

use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory, HasFilter;

    protected $guarded = [''];

    public function orderDetails()
    {
        return $this->belongsToMany(Order::class)
            ->using(Ticket::class)
            ->withTimestamps()
            ->withPivot(['code', 'price', 'id']);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function paymentMethods()
    {
        return $this->belongsToMany(PaymentMethod::class);
    }
}
