<?php

namespace App\Models;

use App\Enums\BalanceTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'color',
        'icon_code',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => BalanceTypeEnum::class,
        ];
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}
