<?php

namespace App\Enums;

enum ResponseStatus: int
{
    case CREATED = 201;
    case UNAUTHENTICATED = 401;
    case BAD_REQUEST = 400;
}
