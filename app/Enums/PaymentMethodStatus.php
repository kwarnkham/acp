<?php

namespace App\Enums;

enum PaymentMethodStatus: int
{
    case OPEN = 1;
    case CLOSE = 2;

    public static function statuses(): array
    {
        return [1, 2];
    }
}
