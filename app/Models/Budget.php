<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max_limit',
        'period',
        'is_recurrent',
        'is_active',
        'expense_amount',
        'percentage_value',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'max_limit' => 'decimal:2',
            'expense_amount' => 'decimal:2',
            'percentage_value' => 'integer',
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'created_at' => 'datetime: Y-m-d H:m',
            'updated_at' => 'datetime: Y-m-d H:m',
            'is_recurrent' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
