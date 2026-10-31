<?php

use App\DataTransferObjects\CategoryData;
use App\DataTransferObjects\CategoryUpdateData;
use App\Enums\BalanceTypeEnum;

it('maps a fully specified payload', function () {
    $data = CategoryData::fromValidated([
        'name' => '  Mascotas ',
        'type' => 'expense',
        'color' => '#1289E7',
        'icon_code' => 'paw',
    ]);

    expect($data->type)->toBe(BalanceTypeEnum::EXPENSE)
        ->and($data->toArray())->toBe([
            'name' => 'Mascotas',
            'type' => 'expense',
            'color' => '#1289E7',
            'icon_code' => 'paw',
        ]);
});

it('falls back to the default color and icon', function () {
    $data = CategoryData::fromValidated(['name' => 'Mascotas', 'type' => 'income']);

    expect($data->color)->toBe('#8C6DE2')
        ->and($data->icon_code)->toBe('tag');
});

it('omits absent fields from the update payload', function () {
    $data = CategoryUpdateData::fromValidated(['name' => ' Hogar ']);

    expect($data->toArray())->toBe(['name' => 'Hogar']);
});

it('maps the type to the balance type enum', function (string $type, BalanceTypeEnum $expected) {
    $data = CategoryData::fromValidated(['name' => 'Mascotas', 'type' => $type]);

    expect($data->type)->toBe($expected)
        ->and($data->toArray()['type'])->toBe($type);
})->with([
    'income' => ['income', BalanceTypeEnum::INCOME],
    'expense' => ['expense', BalanceTypeEnum::EXPENSE],
]);

it('includes the type in the update payload when present', function () {
    $data = CategoryUpdateData::fromValidated(['type' => 'income']);

    expect($data->type)->toBe(BalanceTypeEnum::INCOME)
        ->and($data->toArray())->toBe(['type' => 'income']);
});
