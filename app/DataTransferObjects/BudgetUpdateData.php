<?php

namespace App\DataTransferObjects;

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
        public ?float $expense_amount = null,
        public ?int $percentage_value = null,
        public ?int $category_id = null,
        public ?int $period_id = null,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            max_limit: isset($data['max_limit']) ? (float) $data['max_limit'] : null,
            expense_amount: isset($data['expense_amount']) ? (float) $data['expense_amount'] : null,
            percentage_value: isset($data['percentage_value']) ? (int) $data['percentage_value'] : null,
            category_id: isset($data['category_id']) ? (int) $data['category_id'] : null,
            period_id: isset($data['period_id']) ? (int) $data['period_id'] : null,
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
                'expense_amount' => $this->expense_amount,
                'percentage_value' => $this->percentage_value,
                'category_id' => $this->category_id,
                'period_id' => $this->period_id,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
