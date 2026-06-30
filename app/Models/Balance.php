<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Balance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'description',
        'amount',
        'type',
        'account_name',
        'account_id',
        'balanceable_type',
        'balanceable_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'balanceable_id',
        'balanceable_type'
    ];

    public function casts(): array
    {
        return [
            'amount' => 'decimal:3',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function balanceable(): MorphTo
    {
        return $this->morphTo();
    }
}
