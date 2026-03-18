<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum AccountTypeEnum : string
{
    use EnumToArray;
    
    case CASH = 'cash';
    case DEBIT_CARD = 'debit';
    case CREDIT_CARD = 'credit';
}