<?php

namespace App\DataTransferObjects;

use App\Enums\BalanceTypeEnum;

/**
 * Mirrors Category::$fillable with every property nullable: null means the field
 * was not present in the validated payload, so the repository must leave the
 * existing column untouched. None of these columns accept NULL in the database.
 */
final readonly class CategoryUpdateData
{
    public function __construct(
        public ?string $name = null,
        public ?BalanceTypeEnum $type = null,
        public ?string $color = null,
        public ?string $icon_code = null,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            name: isset($data['name']) ? trim($data['name']) : null,
            type: isset($data['type']) ? BalanceTypeEnum::from($data['type']) : null,
            color: $data['color'] ?? null,
            icon_code: $data['icon_code'] ?? null,
        );
    }

    /**
     * Only fields explicitly present in the source payload are included.
     *
     * @return array{name?: string, type?: string, color?: string, icon_code?: string}
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'name' => $this->name,
                'type' => $this->type?->value,
                'color' => $this->color,
                'icon_code' => $this->icon_code,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
