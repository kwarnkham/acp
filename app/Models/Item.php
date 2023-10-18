<?php

namespace App\Models;

use App\Traits\AppModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory, AppModel;

    protected $guarded = [''];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
