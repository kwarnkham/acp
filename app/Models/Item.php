<?php

namespace App\Models;

class Item extends BaseModel
{

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function pictures()
    {
        return $this->hasMany(Picture::class);
    }
}
