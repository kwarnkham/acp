<?php

namespace App\Enums;

enum OrderStatus: int
{
    case PENDING = 1;
    case PAID = 2;
    case CONFIRMED_PAID = 3;


    public static function statuses(): array
    {
        return [1, 2, 3];
    }
}
