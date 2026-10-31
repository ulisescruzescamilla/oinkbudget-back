<?php

use App\DataTransferObjects\RecurrentBudgetData;
use App\DataTransferObjects\RecurrentBudgetUpdateData;

it('maps a fully specified payload', function () {
    $data = RecurrentBudgetData::fromValidated([
        'name' => '  Renta ',
        'color' => '#1289E7',
        'icon_code' => 'home',
    ]);

    expect($data->toArray())->toBe([
        'name' => 'Renta',
        'color' => '#1289E7',
        'icon_code' => 'home',
    ]);
});

it('falls back to the default color and icon', function () {
    $data = RecurrentBudgetData::fromValidated(['name' => 'Renta']);

    expect($data->color)->toBe('#8C6DE2')
        ->and($data->icon_code)->toBe('tag');
});

it('leaves properties null for fields absent from the update payload', function () {
    $data = RecurrentBudgetUpdateData::fromValidated(['color' => '#1289E7']);

    expect($data->color)->toBe('#1289E7')
        ->and($data->name)->toBeNull()
        ->and($data->icon_code)->toBeNull();
});

it('omits absent fields from the update payload', function () {
    $data = RecurrentBudgetUpdateData::fromValidated(['name' => ' Internet ']);

    expect($data->toArray())->toBe(['name' => 'Internet']);
});
