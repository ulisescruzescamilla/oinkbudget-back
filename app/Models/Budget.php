<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'max_limit',
        'expense_amount',
        'percentage_value',
        'graph_color',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'max_limit' => 'decimal:2',
            'expense_amount' => 'decimal:2',
            'percentage_value' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
