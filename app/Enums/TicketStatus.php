<?php

namespace App\Enums;

enum TicketStatus: int
{
    case AVAILABLE = 1;
    case BOOKED = 2;
    case PAID = 3;
    case CONFIRMED_PAID = 4;


    public static function statuses(): array
    {
        return [1, 2, 3, 4];
    }
}
