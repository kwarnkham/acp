<?php

namespace App\Models;

use App\Traits\HasFilter;
use Illuminate\Support\Facades\Storage;

class Item extends BaseModel
{
    use HasFilter;

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
