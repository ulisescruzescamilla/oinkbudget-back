<?php

namespace App\DataTransferObjects;

use App\Enums\AccountTypeEnum;

final readonly class AccountData
{
    public function __construct(
        public string $name,
        public AccountTypeEnum $type,
        public float $amount,
        public bool $hidden,
        public ?string $client_id = null,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            name: $data['name'],
            type: AccountTypeEnum::from($data['type']),
            amount: (float) $data['amount'],
            hidden: (bool) $data['hidden'],
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
            'type' => $this->type->value,
            'amount' => $this->amount,
            'hidden' => $this->hidden,
            'client_id' => $this->client_id,
        ];
    }
}
