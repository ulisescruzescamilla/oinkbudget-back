<?php

use App\DataTransferObjects\PeriodData;
use App\DataTransferObjects\PeriodUpdateData;

it('maps a fully specified payload', function () {
    $data = PeriodData::fromValidated([
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-30',
        'is_active' => true,
    ]);

    expect($data->toArray())->toBe([
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-30',
        'is_active' => true,
    ]);
});

it('defaults is_active to false', function () {
    $data = PeriodData::fromValidated([
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-30',
    ]);

    expect($data->is_active)->toBeFalse();
});

it('omits absent fields from the update payload', function () {
    $data = PeriodUpdateData::fromValidated(['end_date' => '2026-12-15']);

    expect($data->toArray())->toBe(['end_date' => '2026-12-15']);
});

it('keeps an explicit false is_active in the update payload', function () {
    $data = PeriodUpdateData::fromValidated(['is_active' => false]);

    expect($data->toArray())->toBe(['is_active' => false]);
});
