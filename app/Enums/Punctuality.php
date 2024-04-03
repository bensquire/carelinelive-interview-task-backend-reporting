<?php

namespace App\Enums;
enum Punctuality: string
{
    case OnTime = 'on_time';
    case Late = 'late';
    case Missed = 'missed';
}
