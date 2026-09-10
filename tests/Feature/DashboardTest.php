<?php

use App\Models\Balance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard last moves include both incomes and expenses ordered by latest', function () {
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
    Balance::factory()->count(12)->sequence(fn ($sequence) => [
        'created_at' => now()->subMinutes($sequence->index),
    ])->create();

    $response = $this->getJson('/api/dashboard')->assertOk();

    expect($response->json('last_moves'))->toHaveCount(10);
});
