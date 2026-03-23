<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'amount',
        'hidden',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'hidden' => 'boolean',
        ];
    }
}
