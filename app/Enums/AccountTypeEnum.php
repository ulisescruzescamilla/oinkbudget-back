<?php

namespace App\Enums;

use App\Traits\EnumToArrayTrait;

enum AccountTypeEnum: string
{
    use EnumToArrayTrait;

    case CASH = 'cash';
    case DEBIT_CARD = 'debit_card';
    case CREDIT_CARD = 'credit_card';
}
