<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            // Get value using Mexico city timezone
            get: fn (mixed $value) => Carbon::parse($value)->timezone('America/Mexico_city')->format('Y-m-d H:i'),
            // Save UTC from config
            set: fn ($value) => [
                'created_at' => $value,
            ],
        );
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
