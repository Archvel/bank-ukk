<?php

namespace App\Enums;

enum SaverStatus: string
{
    case Active = 'active';
    case Blocked = 'blocked';
    case Closed = 'closed';
}
