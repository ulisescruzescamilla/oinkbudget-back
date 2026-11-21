<?php

use App\Models\Budget;
use App\Models\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns all periods ordered by most recent start date', function () {
    Period::factory()->create(['start_date' => '2026-01-01', 'end_date' => '2026-01-31']);
    Period::factory()->create(['start_date' => '2026-03-01', 'end_date' => '2026-03-31']);
    Period::factory()->create(['start_date' => '2026-02-01', 'end_date' => '2026-02-28']);

    $this->getJson('/api/periods')
        ->assertOk()
        ->assertJsonCount(3)
        ->assertJsonPath('0.start_date', '2026-03-01')
        ->assertJsonPath('2.start_date', '2026-01-01');
});

it('creates a period', function () {
    $this->postJson('/api/periods', [
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-30',
        'is_active' => true,
    ])
        ->assertCreated()
        ->assertJsonPath('start_date', '2026-11-01')
        ->assertJsonPath('end_date', '2026-11-30')
        ->assertJsonPath('is_active', true);

    $this->assertDatabaseHas('periods', [
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-30',
        'is_active' => true,
    ]);
});

it('creates an inactive period by default', function () {
    $this->postJson('/api/periods', [
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-30',
    ])
        ->assertCreated()
        ->assertJsonPath('is_active', false);
});

it('validates the period fields on store', function (array $payload, string $field) {
    $this->postJson('/api/periods', [
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-30',
        ...$payload,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$field]);
})->with([
    'start_date missing' => [['start_date' => null], 'start_date'],
    'end_date missing' => [['end_date' => null], 'end_date'],
    'start_date bad format' => [['start_date' => '01/11/2026'], 'start_date'],
    'end_date bad format' => [['end_date' => '30/11/2026'], 'end_date'],
    'end_date before start_date' => [['end_date' => '2026-10-31'], 'end_date'],
    'end_date equal to start_date' => [['end_date' => '2026-11-01'], 'end_date'],
    'is_active not boolean' => [['is_active' => 'yes'], 'is_active'],
]);

it('deactivates the previously active period when creating an active one', function () {
    $previous = Period::factory()->active()->create();

    $this->postJson('/api/periods', [
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-30',
        'is_active' => true,
    ])->assertCreated();

    expect($previous->fresh()->is_active)->toBeFalse()
        ->and(Period::query()->where('is_active', true)->count())->toBe(1);
});

it('keeps the active period when creating an inactive one', function () {
    $active = Period::factory()->active()->create();

    $this->postJson('/api/periods', [
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-30',
    ])->assertCreated();

    expect($active->fresh()->is_active)->toBeTrue();
});

it('updates a period', function () {
    $period = Period::factory()->create(['start_date' => '2026-11-01', 'end_date' => '2026-11-30']);

    $this->putJson("/api/periods/{$period->id}", ['end_date' => '2026-12-15'])
        ->assertOk()
        ->assertJsonPath('start_date', '2026-11-01')
        ->assertJsonPath('end_date', '2026-12-15');
});

it('validates the dates against the stored period on update', function (array $payload, string $field) {
    $period = Period::factory()->create(['start_date' => '2026-11-01', 'end_date' => '2026-11-30']);

    $this->putJson("/api/periods/{$period->id}", $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$field]);
})->with([
    'end_date before stored start_date' => [['end_date' => '2026-10-15'], 'end_date'],
    'start_date after stored end_date' => [['start_date' => '2026-12-01'], 'start_date'],
    'end_date before new start_date' => [['start_date' => '2026-11-10', 'end_date' => '2026-11-05'], 'end_date'],
]);

it('deactivates the other periods when activating one on update', function () {
    $previous = Period::factory()->active()->create();
    $period = Period::factory()->create();

    $this->putJson("/api/periods/{$period->id}", ['is_active' => true])
        ->assertOk()
        ->assertJsonPath('is_active', true);

    expect($previous->fresh()->is_active)->toBeFalse();
});

it('deletes a period and unlinks its budgets', function () {
    $period = Period::factory()->create();
    $budget = Budget::factory()->create(['period_id' => $period->id]);

    $this->deleteJson("/api/periods/{$period->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('periods', ['id' => $period->id]);
    expect($budget->fresh()->period_id)->toBeNull();
});

it('returns 404 for a non-existent period', function () {
    $this->putJson('/api/periods/9999', ['is_active' => true])->assertNotFound();
    $this->deleteJson('/api/periods/9999')->assertNotFound();
});

it('lists the budgets of a period', function () {
    $period = Period::factory()->create();
    Budget::factory()->count(2)->create(['period_id' => $period->id]);
    Budget::factory()->create();

    expect($period->budgets)->toHaveCount(2)
        ->and($period->budgets->first()->budgetPeriod->is($period))->toBeTrue();
});
