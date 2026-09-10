<?php

use App\Models\Balance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns only today balances by default', function () {
    Balance::factory()->count(3)->create(['created_at' => Carbon::today()]);
    Balance::factory()->count(2)->create(['created_at' => Carbon::yesterday()]);

    $this->getJson('/api/balances')
        ->assertOk()
        ->assertJsonCount(3);
});

it('filters balances by date range', function () {
    Balance::factory()->create(['created_at' => '2026-06-01']);
    Balance::factory()->create(['created_at' => '2026-06-15']);
    Balance::factory()->create(['created_at' => '2026-07-01']);

    $this->getJson('/api/balances?start_date=2026-06-01&end_date=2026-06-30')
        ->assertOk()
        ->assertJsonCount(2);
});

it('returns balances ordered by created_at desc by default', function () {
    Balance::factory()->create(['created_at' => '2026-06-01']);
    Balance::factory()->create(['created_at' => '2026-06-02']);

    $response = $this->getJson('/api/balances?start_date=2026-06-01&end_date=2026-06-30')
        ->assertOk();

    $data = $response->json();

    expect($data[0]['created_at'])->toBe('2026-06-02T00:00:00.000000Z');
    expect($data[1]['created_at'])->toBe('2026-06-01T00:00:00.000000Z');
});

it('returns balances ordered by created_at asc', function () {
    Balance::factory()->create(['created_at' => '2026-06-01']);
    Balance::factory()->create(['created_at' => '2026-06-02']);

    $response = $this->getJson('/api/balances?start_date=2026-06-01&end_date=2026-06-30&order=asc')
        ->assertOk();

    $data = $response->json();

    expect($data[0]['created_at'])->toBe('2026-06-01T00:00:00.000000Z');
    expect($data[1]['created_at'])->toBe('2026-06-02T00:00:00.000000Z');
});

it('validates date format for date filters', function () {
    $this->getJson('/api/balances?start_date=01-06-2026&end_date=30-06-2026')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date', 'end_date']);
});

it('validates order parameter', function () {
    $this->getJson('/api/balances?order=invalid')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['order']);
});

it('filters balances by range=week', function () {
    Balance::factory()->create(['created_at' => Carbon::today()]);
    Balance::factory()->create(['created_at' => Carbon::today()->subDays(6)]);
    Balance::factory()->create(['created_at' => Carbon::today()->subDays(7)]);

    $this->getJson('/api/balances?range=week')
        ->assertOk()
        ->assertJsonCount(2);
});

it('filters balances by range=month', function () {
    Balance::factory()->create(['created_at' => Carbon::today()]);
    Balance::factory()->create(['created_at' => Carbon::today()->subDays(29)]);
    Balance::factory()->create(['created_at' => Carbon::today()->subDays(30)]);

    $this->getJson('/api/balances?range=month')
        ->assertOk()
        ->assertJsonCount(2);
});

it('returns every balance when range=all', function () {
    Balance::factory()->create(['created_at' => Carbon::today()]);
    Balance::factory()->create(['created_at' => Carbon::today()->subYear()]);

    $this->getJson('/api/balances?range=all')
        ->assertOk()
        ->assertJsonCount(2);
});

it('filters balances by type=income', function () {
    Balance::factory()->create(['type' => 'income', 'created_at' => Carbon::today()]);
    Balance::factory()->create(['type' => 'expense', 'created_at' => Carbon::today()]);

    $response = $this->getJson('/api/balances?type=income')
        ->assertOk()
        ->assertJsonCount(1);

    expect($response->json('0.type'))->toBe('income');
});

it('filters balances by type=expense', function () {
    Balance::factory()->create(['type' => 'income', 'created_at' => Carbon::today()]);
    Balance::factory()->create(['type' => 'expense', 'created_at' => Carbon::today()]);

    $response = $this->getJson('/api/balances?type=expense')
        ->assertOk()
        ->assertJsonCount(1);

    expect($response->json('0.type'))->toBe('expense');
});

it('combines range and type filters', function () {
    Balance::factory()->create(['type' => 'income', 'created_at' => Carbon::today()]);
    Balance::factory()->create(['type' => 'expense', 'created_at' => Carbon::today()]);
    Balance::factory()->create(['type' => 'income', 'created_at' => Carbon::today()->subDays(10)]);

    $this->getJson('/api/balances?range=week&type=income')
        ->assertOk()
        ->assertJsonCount(1);
});

it('validates range parameter', function () {
    $this->getJson('/api/balances?range=invalid')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['range']);
});

it('validates type parameter', function () {
    $this->getJson('/api/balances?type=invalid')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

it('includes account relationship', function () {
    $balance = Balance::factory()->create(['created_at' => Carbon::today()]);

    $response = $this->getJson('/api/balances')
        ->assertOk();

    $data = $response->json();

    expect($data[0])->toHaveKey('account');
});
