<?php

namespace App\DataTransferObjects;

use App\Enums\PeriodEnum;

final readonly class BudgetData
{
    public function __construct(
        public string $name,
        public ?float $max_limit,
        public PeriodEnum $period,
        public bool $is_recurrent,
        public bool $is_active,
        public float $expense_amount,
        public ?int $percentage_value,
        public string $start_date,
        public string $end_date,
        public ?int $category_id = null,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            name: $data['name'],
            max_limit: isset($data['max_limit']) ? (float) $data['max_limit'] : null,
            period: PeriodEnum::from($data['period']),
            is_recurrent: (bool) ($data['is_recurrent'] ?? false),
            is_active: (bool) ($data['is_active'] ?? true),
            expense_amount: (float) ($data['expense_amount'] ?? 0),
            percentage_value: isset($data['percentage_value']) ? (int) $data['percentage_value'] : null,
            start_date: $data['start_date'],
            end_date: $data['end_date'],
            category_id: isset($data['category_id']) ? (int) $data['category_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'max_limit' => $this->max_limit,
            'period' => $this->period->value,
            'is_recurrent' => $this->is_recurrent,
            'is_active' => $this->is_active,
            'expense_amount' => $this->expense_amount,
            'percentage_value' => $this->percentage_value,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'category_id' => $this->category_id,
        ];
    }
}
