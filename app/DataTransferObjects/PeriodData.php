<?php

namespace App\DataTransferObjects;

final readonly class PeriodData
{
    public function __construct(
        public string $start_date,
        public string $end_date,
        public bool $is_active = false,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            start_date: $data['start_date'],
            end_date: $data['end_date'],
            is_active: (bool) ($data['is_active'] ?? false),
        );
    }

    /**
     * @return array{start_date: string, end_date: string, is_active: bool}
     */
    public function toArray(): array
    {
        return [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
        ];
    }
}
