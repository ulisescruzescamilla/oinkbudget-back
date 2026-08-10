<?php

namespace App\DataTransferObjects;

final readonly class IncomeData
{
    public function __construct(
        public float $amount,
        public string $description,
        public int $account_id,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            amount: (float) $data['amount'],
            description: $data['description'],
            account_id: (int) $data['account_id'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'description' => $this->description,
            'account_id' => $this->account_id,
        ];
    }
}
