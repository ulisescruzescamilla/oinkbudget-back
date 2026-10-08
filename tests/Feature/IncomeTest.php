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

it('normalizes an offline-provided created_at with a non-UTC offset to the correct UTC instant', function () {
    $account = Account::factory()->create();
    $backdated = now()->subDays(3)->startOfSecond();

    $data = [
        'amount' => 750.00,
        'description' => 'Offline freelance payment (local offset)',
        'account_id' => $account->id,
        'created_at' => $backdated->copy()->setTimezone('America/Mexico_City')->toIso8601String(),
    ];

    $response = $this->postJson('/api/incomes', $data)->assertCreated();
    $incomeId = $response->json('id');

    $this->assertDatabaseHas('incomes', [
        'id' => $incomeId,
        'created_at' => $backdated->toDateTimeString(),
    ]);
});

it('rejects a bare date created_at without an explicit offset', function () {
    $account = Account::factory()->create();

    $data = [
        'amount' => 300.00,
        'description' => 'Freelance',
        'account_id' => $account->id,
        'created_at' => '2026-06-29',
    ];

    $this->postJson('/api/incomes', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['created_at']);
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

    expect($income->fresh()->deleted_at)->not->toBeNull();
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

it('adds the income amount to the selected account on create', function () {
    $account = Account::factory()->create(['amount' => 1000]);

    $this->postJson('/api/incomes', [
        'amount' => 250.50,
        'description' => 'Freelance',
        'account_id' => $account->id,
    ])->assertCreated();

    expect($account->refresh()->amount)->toBe('1250.50');
});

it('adds the income amount to the account only once when client_id is duplicated', function () {
    $account = Account::factory()->create(['amount' => 1000]);

    $data = [
        'amount' => 100,
        'description' => 'Freelance',
        'account_id' => $account->id,
        'client_id' => (string) Str::uuid(),
    ];

    $this->postJson('/api/incomes', $data)->assertCreated();
    $this->postJson('/api/incomes', $data)->assertCreated();

    expect($account->refresh()->amount)->toBe('1100.00');
});

it('adjusts the account by the difference when the income amount is updated', function () {
    $account = Account::factory()->create(['amount' => 1000]);

    $incomeId = $this->postJson('/api/incomes', [
        'amount' => 100,
        'description' => 'Freelance',
        'account_id' => $account->id,
    ])->json('id');

    $this->putJson("/api/incomes/{$incomeId}", [
        'amount' => 300,
        'description' => 'Freelance',
        'account_id' => $account->id,
    ])->assertOk();

    expect($account->refresh()->amount)->toBe('1300.00');
});

it('moves the income amount between accounts when the account is changed on update', function () {
    $oldAccount = Account::factory()->create(['amount' => 1000]);
    $newAccount = Account::factory()->create(['amount' => 500]);

    $incomeId = $this->postJson('/api/incomes', [
        'amount' => 100,
        'description' => 'Freelance',
        'account_id' => $oldAccount->id,
    ])->json('id');

    $this->putJson("/api/incomes/{$incomeId}", [
        'amount' => 150,
        'description' => 'Freelance',
        'account_id' => $newAccount->id,
    ])->assertOk();

    expect($oldAccount->refresh()->amount)->toBe('1000.00')
        ->and($newAccount->refresh()->amount)->toBe('650.00');
});

it('removes the income amount from the account on delete', function () {
    $account = Account::factory()->create(['amount' => 1000]);

    $incomeId = $this->postJson('/api/incomes', [
        'amount' => 100,
        'description' => 'Freelance',
        'account_id' => $account->id,
    ])->json('id');

    $this->deleteJson("/api/incomes/{$incomeId}")->assertNoContent();

    expect($account->refresh()->amount)->toBe('1000.00');
});

it('keeps the income row in the database when it is soft deleted', function () {
    $income = Income::factory()->create();

    $this->deleteJson("/api/incomes/{$income->id}")->assertNoContent();

    $this->assertSoftDeleted('incomes', ['id' => $income->id]);
    expect(Income::query()->find($income->id))->toBeNull()
        ->and(Income::withTrashed()->find($income->id))->not->toBeNull();
});

it('excludes soft deleted incomes from the list', function () {
    $kept = Income::factory()->create();
    Income::factory()->create()->delete();

    $response = $this->getJson('/api/incomes')
        ->assertOk()
        ->assertJsonCount(1);

    expect($response->json('0.id'))->toBe($kept->id);
});

it('soft deletes the balance record when its income is deleted', function () {
    $incomeId = $this->postJson('/api/incomes', [
        'amount' => 100,
        'description' => 'Freelance',
        'account_id' => Account::factory()->create()->id,
    ])->json('id');

    $balance = Income::query()->findOrFail($incomeId)->balance;

    $this->deleteJson("/api/incomes/{$incomeId}")->assertNoContent();

    $this->assertSoftDeleted('incomes', ['id' => $incomeId]);
    $this->assertSoftDeleted('balances', ['id' => $balance->id]);
});

it('returns 404 when updating or deleting a soft deleted income', function () {
    $income = Income::factory()->create();
    $income->delete();

    $this->putJson("/api/incomes/{$income->id}", [
        'amount' => 100.00,
        'description' => 'Test',
        'account_id' => $income->account_id,
    ])->assertNotFound();

    $this->deleteJson("/api/incomes/{$income->id}")->assertNotFound();
});

it('does not remove the income amount twice when delete is repeated', function () {
    $account = Account::factory()->create(['amount' => 1000]);

    $incomeId = $this->postJson('/api/incomes', [
        'amount' => 100,
        'description' => 'Freelance',
        'account_id' => $account->id,
    ])->json('id');

    $this->deleteJson("/api/incomes/{$incomeId}")->assertNoContent();
    $this->deleteJson("/api/incomes/{$incomeId}")->assertNotFound();

    expect($account->refresh()->amount)->toBe('1000.00');
});
