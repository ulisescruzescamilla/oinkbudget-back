<?php

namespace App\DataTransferObjects;

final readonly class RecurrentBudgetData
{
    public const string DEFAULT_COLOR = '#8C6DE2';

    public const string DEFAULT_ICON_CODE = 'tag';

    public function __construct(
        public string $name,
        public string $color = self::DEFAULT_COLOR,
        public string $icon_code = self::DEFAULT_ICON_CODE,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            name: trim($data['name']),
            color: $data['color'] ?? self::DEFAULT_COLOR,
            icon_code: $data['icon_code'] ?? self::DEFAULT_ICON_CODE,
        );
    }

    /**
     * @return array{name: string, color: string, icon_code: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'color' => $this->color,
            'icon_code' => $this->icon_code,
        ];
    }
}
