<?php

use App\Models\Balance;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard page returns 404 for guests because there is no frontend', function () {
    $response = $this->get(route('dashboard'));
    $response->assertNotFound();
});

test('dashboard page returns 404 for authenticated users because there is no frontend', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertNotFound();
});

test('dashboard last moves include both incomes and expenses ordered by latest', function () {
    $this->travelTo(now()->setTime(12, 0));

    $older = Balance::factory()->create([
        'type' => 'expense',
        'created_at' => now()->subHours(2),
    ]);
    $newer = Balance::factory()->create([
        'type' => 'income',
        'created_at' => now()->subHour(),
    ]);
    Balance::factory()->create(['created_at' => now()->subDay()]);

    $response = $this->getJson('/api/dashboard')->assertOk();

    $lastMoves = $response->json('last_moves');

    expect($lastMoves)->toHaveCount(2);
    expect($lastMoves[0]['id'])->toBe($newer->id);
    expect($lastMoves[1]['id'])->toBe($older->id);
});

test('dashboard last moves are capped at the ten most recent records', function () {
    $this->travelTo(now()->setTime(12, 0));

    Balance::factory()->count(12)->sequence(fn ($sequence) => [
        'created_at' => now()->subMinutes($sequence->index),
    ])->create();

    $response = $this->getJson('/api/dashboard')->assertOk();

    expect($response->json('last_moves'))->toHaveCount(10);
});

test('dashboard last moves exclude soft deleted balances', function () {
    $this->travelTo(now()->setTime(12, 0));

    $kept = Balance::factory()->create(['created_at' => now()->subHour()]);
    $deleted = Balance::factory()->create(['created_at' => now()->subMinutes(30)]);
    $deleted->delete();

    $response = $this->getJson('/api/dashboard')->assertOk();

    expect($response->json('last_moves'))->toHaveCount(1)
        ->and($response->json('last_moves.0.id'))->toBe($kept->id);
});

test('dashboard totals exclude soft deleted expenses', function () {
    $this->travelTo(now()->setTime(12, 0));

    Expense::factory()->create(['amount' => 100]);
    Expense::factory()->create(['amount' => 40])->delete();

    $response = $this->getJson('/api/dashboard')->assertOk();

    expect((float) $response->json('total_expense_today'))->toBe(100.0)
        ->and((float) collect($response->json('trend'))->last()['v'])->toBe(100.0);
});
