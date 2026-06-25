<?php

namespace App\Enums;

use App\Traits\EnumToArrayTrait;

enum PeriodEnum: string
{
    use EnumToArrayTrait;

    case BIWEEKLY = 'biweekly';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
}
