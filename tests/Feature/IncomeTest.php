<?php

use App\Models\Account;
use App\Models\Balance;
use App\Models\Income;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('returns all incomes', function () {
    Income::factory()->count(3)->create();

    $this->getJson('/api/incomes')
        ->assertOk()
        ->assertJsonCount(3);
});

it('creates an income', function () {
    $account = Account::factory()->create();

    $data = [
        'amount' => 1500.00,
        'description' => 'Monthly salary',
        'account_id' => $account->id,
    ];

    $this->postJson('/api/incomes', $data)
        ->assertCreated()
        ->assertJsonFragment(['description' => 'Monthly salary']);

    $this->assertDatabaseHas('incomes', ['description' => 'Monthly salary']);
});

it('deduplicates income creation via client_id without doubling the balance side effect', function () {
    $account = Account::factory()->create();
    $clientId = (string) Str::uuid();

    $data = [
        'amount' => 1500.00,
        'description' => 'Monthly salary',
        'account_id' => $account->id,
        'client_id' => $clientId,
    ];

    $firstResponse = $this->postJson('/api/incomes', $data)->assertCreated();
    $secondResponse = $this->postJson('/api/incomes', $data)->assertCreated();

    $firstId = json_decode($firstResponse->getContent(), true)['id'];
    $secondId = json_decode($secondResponse->getContent(), true)['id'];

    expect($secondId)->toBe($firstId);
    $this->assertDatabaseCount('incomes', 1);

    expect(Balance::query()
        ->where('balanceable_type', Income::class)
        ->where('balanceable_id', $firstId)
        ->count())->toBe(1);
});

it('preserves an offline-provided created_at on the income and its balance record', function () {
    $account = Account::factory()->create();
    $backdated = now()->subDays(3)->startOfSecond();

    $data = [
        'amount' => 750.00,
        'description' => 'Offline freelance payment',
        'account_id' => $account->id,
        'created_at' => $backdated->toIso8601String(),
    ];

    $response = $this->postJson('/api/incomes', $data)->assertCreated();
    $incomeId = $response->json('id');

    $this->assertDatabaseHas('incomes', [
        'id' => $incomeId,
        'created_at' => $backdated->toDateTimeString(),
    ]);

    $balance = Balance::query()->where('balanceable_type', Income::class)->where('balanceable_id', $incomeId)->first();

    expect($balance->getRawOriginal('created_at'))->toBe($backdated->toDateTimeString());
});

it('defaults created_at to now when not provided', function () {
    $account = Account::factory()->create();

    $data = [
        'amount' => 300.00,
        'description' => 'Freelance',
        'account_id' => $account->id,
    ];

    $this->postJson('/api/incomes', $data)->assertCreated();

    $income = Income::query()->where('description', 'Freelance')->firstOrFail();

    expect($income->created_at->diffInSeconds(now()))->toBeLessThan(5);
});

it('validates required fields on store', function () {
    $this->postJson('/api/incomes', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount', 'description', 'account_id']);
});

it('validates account_id exists', function () {
    $data = [
        'amount' => 500.00,
        'description' => 'Freelance',
        'account_id' => 999,
    ];

    $this->postJson('/api/incomes', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['account_id']);
});

it('validates amount is numeric and non-negative', function () {
    $account = Account::factory()->create();

    $data = [
        'amount' => -100,
        'description' => 'Test',
        'account_id' => $account->id,
    ];

    $this->postJson('/api/incomes', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount']);
});

it('updates an income', function () {
    $income = Income::factory()->create();
    $newAccount = Account::factory()->create();

    $data = [
        'amount' => 2000.00,
        'description' => 'Updated salary',
        'account_id' => $newAccount->id,
    ];

    $this->putJson("/api/incomes/{$income->id}", $data)
        ->assertOk()
        ->assertJsonFragment(['description' => 'Updated salary']);

    $this->assertDatabaseHas('incomes', ['id' => $income->id, 'description' => 'Updated salary']);
});

it('deletes an income', function () {
    $income = Income::factory()->create();

    $this->deleteJson("/api/incomes/{$income->id}")->assertNoContent();

    $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
});

it('returns 404 when updating a non-existent income', function () {
    $account = Account::factory()->create();

    $data = [
        'amount' => 100.00,
        'description' => 'Test',
        'account_id' => $account->id,
    ];

    $this->putJson('/api/incomes/999', $data)->assertNotFound();
});

it('returns 404 when deleting a non-existent income', function () {
    $this->deleteJson('/api/incomes/999')->assertNotFound();
});
