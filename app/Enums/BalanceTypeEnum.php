<?php

namespace App\Enums;

use App\Traits\EnumToArrayTrait;

enum BalanceTypeEnum: string
{
    use EnumToArrayTrait;

    case INCOME = 'income';
    case EXPENSE = 'expense';
}
