<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

final readonly class IncomeData
{
    public function __construct(
        public float $amount,
        public string $description,
        public int $account_id,
        public ?string $client_id = null,
        public ?string $created_at = null,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            amount: (float) $data['amount'],
            description: $data['description'],
            account_id: (int) $data['account_id'],
            client_id: $data['client_id'] ?? null,
            created_at: $data['created_at'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'amount' => $this->amount,
            'description' => $this->description,
            'account_id' => $this->account_id,
            'client_id' => $this->client_id,
        ];

        if ($this->created_at !== null) {
            $data['created_at'] = Carbon::parse($this->created_at);
        }

        return $data;
    }
}
