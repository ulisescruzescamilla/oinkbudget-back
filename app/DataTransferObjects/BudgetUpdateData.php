<?php

namespace App\DataTransferObjects;

use App\Enums\PeriodEnum;

/**
 * Mirrors Budget::$fillable with every property nullable: null means the field
 * was not present in the validated payload, so the repository must leave the
 * existing column untouched. None of these columns accept a real NULL value in
 * the database, so there is no "explicit null" case to disambiguate from "absent".
 */
final readonly class BudgetUpdateData
{
    public function __construct(
        public ?string $name = null,
        public ?float $max_limit = null,
        public ?PeriodEnum $period = null,
        public ?bool $is_recurrent = null,
        public ?bool $is_active = null,
        public ?float $expense_amount = null,
        public ?int $percentage_value = null,
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?int $category_id = null,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            max_limit: isset($data['max_limit']) ? (float) $data['max_limit'] : null,
            period: isset($data['period']) ? PeriodEnum::from($data['period']) : null,
            is_recurrent: array_key_exists('is_recurrent', $data) ? (bool) $data['is_recurrent'] : null,
            is_active: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            expense_amount: isset($data['expense_amount']) ? (float) $data['expense_amount'] : null,
            percentage_value: isset($data['percentage_value']) ? (int) $data['percentage_value'] : null,
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            category_id: isset($data['category_id']) ? (int) $data['category_id'] : null,
        );
    }

    /**
     * Only fields explicitly present in the source payload are included.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'name' => $this->name,
                'max_limit' => $this->max_limit,
                'period' => $this->period?->value,
                'is_recurrent' => $this->is_recurrent,
                'is_active' => $this->is_active,
                'expense_amount' => $this->expense_amount,
                'percentage_value' => $this->percentage_value,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'category_id' => $this->category_id,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
