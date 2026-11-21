<?php

namespace App\DataTransferObjects;

final readonly class BudgetData
{
    public function __construct(
        public string $name,
        public ?float $max_limit,
        public float $expense_amount,
        public ?int $percentage_value,
        public ?int $category_id = null,
        public ?int $period_id = null,
        public ?string $client_id = null,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            name: $data['name'],
            max_limit: isset($data['max_limit']) ? (float) $data['max_limit'] : null,
            expense_amount: (float) ($data['expense_amount'] ?? 0),
            percentage_value: isset($data['percentage_value']) ? (int) $data['percentage_value'] : null,
            category_id: isset($data['category_id']) ? (int) $data['category_id'] : null,
            period_id: isset($data['period_id']) ? (int) $data['period_id'] : null,
            client_id: $data['client_id'] ?? null,
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
            'expense_amount' => $this->expense_amount,
            'percentage_value' => $this->percentage_value,
            'category_id' => $this->category_id,
            'period_id' => $this->period_id,
            'client_id' => $this->client_id,
        ];
    }
}
