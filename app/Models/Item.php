<?php

namespace App\Models;

use App\Traits\HasFilter;
use Illuminate\Support\Facades\Storage;

class Item extends BaseModel
{
    use HasFilter;

    protected $with = ['pictures'];

    public function pictures()
    {
        return $this->hasMany(Picture::class);
    }

    public function rounds()
    {
        return $this->hasMany(Round::class);
    }

    public function latestRound()
    {
        return $this->hasOne(Round::class)->latestOfMany();
    }


    public function storePictures(array $pictures)
    {
        foreach ($pictures as $picture) {
            $path = Storage::putFile('items', $picture);
            $this->pictures()->create(['name' => $path]);
        }
    }
}
