<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max_limit',
        'is_recurrent',
        'expense_amount',
        'percentage_value',
        'category_id',
        'period_id',
        'client_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    protected function casts(): array
    {
        return [
            'max_limit' => 'decimal:2',
            'expense_amount' => 'decimal:2',
            'percentage_value' => 'integer',
            'created_at' => 'datetime:Y-m-d H:i',
            'updated_at' => 'datetime:Y-m-d H:i',
            'is_recurrent' => 'boolean',
        ];
    }
}
