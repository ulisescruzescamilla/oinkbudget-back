<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'name',
        'max_limit', // amount of maximum budget
        'expense_amount', // amount expense from budget
        'percentage_value', // percentage taked from incomes
        'color' // hex value color, necesary for graphs
    ];
}
