<?php

namespace App\Enums;

enum RoundStatus: int
{
    case ONGOING = 1;
    case SETTLED = 2;
    case CLOSED = 3;

    public static function statuses(): array
    {
        return [1, 2, 3];
    }
}
