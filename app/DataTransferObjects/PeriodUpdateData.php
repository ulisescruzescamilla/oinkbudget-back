<?php

namespace App\DataTransferObjects;

/**
 * Mirrors Period::$fillable with every property nullable: null means the field
 * was not present in the validated payload, so the repository must leave the
 * existing column untouched. None of these columns accept NULL in the database.
 */
final readonly class PeriodUpdateData
{
    public function __construct(
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?bool $is_active = null,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            is_active: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
        );
    }

    /**
     * Only fields explicitly present in the source payload are included.
     *
     * @return array{start_date?: string, end_date?: string, is_active?: bool}
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'is_active' => $this->is_active,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
