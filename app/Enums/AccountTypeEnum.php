<?php

namespace App\Enums;

use App\Traits\EnumToArrayTrait;

enum AccountTypeEnum : string
{
    use EnumToArrayTrait;
    
    case CASH = 'cash';
    case DEBIT_CARD = 'debit';
    case CREDIT_CARD = 'credit';
}