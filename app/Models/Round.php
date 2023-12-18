<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory, HasFilter;

    protected $guarded = [''];

    protected $appends = ['progress_percentage'];

    public function orderDetails()
    {
        return $this->belongsToMany(Order::class)
            ->using(Ticket::class)
            ->withTimestamps()
            ->withPivot(['code', 'price', 'id']);
    }

    protected function progressPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                $completed = $this->orderDetails()->where('status', OrderStatus::CONFIRMED_PAID->value)->count();
                return ($completed / $this->max_tickets) * 100;
            }
        );
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
