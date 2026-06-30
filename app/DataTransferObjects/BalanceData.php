<?php

namespace App\DataTransferObjects;

use App\Enums\BalanceTypeEnum;

final readonly class BalanceData
{
    public function __construct(
        public string $description,
        public float $amount,
        public BalanceTypeEnum $type,
        public string $account_name,
        public int $account_id,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            description: $data['description'],
            amount: (float) $data['amount'],
            type: BalanceTypeEnum::from($data['type']),
            account_name: $data['account_name'],
            account_id: (int) $data['account_id'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'amount' => $this->amount,
            'type' => $this->type->value,
            'account_name' => $this->account_name,
            'account_id' => $this->account_id,
        ];
    }
}
