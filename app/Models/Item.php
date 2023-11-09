<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

class Item extends BaseModel
{

    protected $with = ['pictures'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function pictures()
    {
        return $this->hasMany(Picture::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function storePictures(array $pictures)
    {
        foreach ($pictures as $picture) {
            $path = Storage::putFile('items', $picture);
            $this->pictures()->create(['name' => $path]);
        }
    }
}
