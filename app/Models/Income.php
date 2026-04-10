<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'description',
        'account_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:3',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
