<?php

use App\Models\Account;
use App\Models\Balance;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('returns all expenses', function () {
    Expense::factory()->count(3)->create();

    $this->getJson('/api/expenses')
        ->assertOk()
        ->assertJsonCount(3);
});

it('creates an expense', function () {
    $budget = Budget::factory()->create();
    $account = Account::factory()->create();

    $data = [
        'amount' => 99.99,
        'description' => 'Groceries',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
    ];

    $this->postJson('/api/expenses', $data)
        ->assertCreated()
        ->assertJsonFragment(['description' => 'Groceries']);

    $this->assertDatabaseHas('expenses', ['description' => 'Groceries']);
});

it('deduplicates expense creation via client_id without doubling budget or balance side effects', function () {
    $budget = Budget::factory()->create(['expense_amount' => 0]);
    $account = Account::factory()->create();
    $clientId = (string) Str::uuid();

    $data = [
        'amount' => 99.99,
        'description' => 'Groceries',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
        'client_id' => $clientId,
    ];

    $firstResponse = $this->postJson('/api/expenses', $data)->assertCreated();
    $secondResponse = $this->postJson('/api/expenses', $data)->assertCreated();

    $firstId = json_decode($firstResponse->getContent(), true)['id'];
    $secondId = json_decode($secondResponse->getContent(), true)['id'];

    expect($secondId)->toBe($firstId);
    $this->assertDatabaseCount('expenses', 1);

    expect((float) $budget->refresh()->expense_amount)->toBe(99.99);

    expect(Balance::query()
        ->where('balanceable_type', Expense::class)
        ->where('balanceable_id', $firstId)
        ->count())->toBe(1);
});

it('preserves an offline-provided created_at on the expense and its balance record', function () {
    $budget = Budget::factory()->create();
    $account = Account::factory()->create();
    $backdated = now()->subDays(3)->startOfSecond();

    $data = [
        'amount' => 42.50,
        'description' => 'Offline groceries',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
        'created_at' => $backdated->toIso8601String(),
    ];

    $response = $this->postJson('/api/expenses', $data)->assertCreated();
    $expenseId = $response->json('id');

    $this->assertDatabaseHas('expenses', [
        'id' => $expenseId,
        'created_at' => $backdated->toDateTimeString(),
    ]);

    $balance = Balance::query()->where('balanceable_type', Expense::class)->where('balanceable_id', $expenseId)->first();

    expect($balance->getRawOriginal('created_at'))->toBe($backdated->toDateTimeString());
});

it('defaults created_at to now when not provided', function () {
    $budget = Budget::factory()->create();
    $account = Account::factory()->create();

    $data = [
        'amount' => 20.00,
        'description' => 'Coffee',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
    ];

    $this->postJson('/api/expenses', $data)->assertCreated();

    $expense = Expense::query()->where('description', 'Coffee')->firstOrFail();

    expect($expense->created_at->diffInSeconds(now()))->toBeLessThan(5);
});

it('validates required fields on store', function () {
    $this->postJson('/api/expenses', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount', 'description', 'budget_id', 'account_id']);
});

it('validates budget_id exists', function () {
    $account = Account::factory()->create();

    $data = [
        'amount' => 50.00,
        'description' => 'Test',
        'budget_id' => 999,
        'account_id' => $account->id,
    ];

    $this->postJson('/api/expenses', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['budget_id']);
});

it('validates account_id exists', function () {
    $budget = Budget::factory()->create();

    $data = [
        'amount' => 50.00,
        'description' => 'Test',
        'budget_id' => $budget->id,
        'account_id' => 999,
    ];

    $this->postJson('/api/expenses', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['account_id']);
});

it('updates an expense', function () {
    $expense = Expense::factory()->create();
    $newBudget = Budget::factory()->create();
    $newAccount = Account::factory()->create();

    $data = [
        'amount' => 200.00,
        'description' => 'Updated description',
        'budget_id' => $newBudget->id,
        'account_id' => $newAccount->id,
    ];

    $this->putJson("/api/expenses/{$expense->id}", $data)
        ->assertOk()
        ->assertJsonFragment(['description' => 'Updated description']);

    $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'description' => 'Updated description']);
});

it('deletes an expense', function () {
    $expense = Expense::factory()->create();

    $this->deleteJson("/api/expenses/{$expense->id}")->assertNoContent();

    $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
});

it('returns 404 when updating a non-existent expense', function () {
    $budget = Budget::factory()->create();
    $account = Account::factory()->create();

    $data = [
        'amount' => 100.00,
        'description' => 'Test',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
    ];

    $this->putJson('/api/expenses/999', $data)->assertNotFound();
});

it('returns 404 when deleting a non-existent expense', function () {
    $this->deleteJson('/api/expenses/999')->assertNotFound();
});
