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

it('normalizes an offline-provided created_at with a non-UTC offset to the correct UTC instant', function () {
    $budget = Budget::factory()->create();
    $account = Account::factory()->create();
    $backdated = now()->subDays(3)->startOfSecond();

    $data = [
        'amount' => 42.50,
        'description' => 'Offline groceries (local offset)',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
        'created_at' => $backdated->copy()->setTimezone('America/Mexico_City')->toIso8601String(),
    ];

    $response = $this->postJson('/api/expenses', $data)->assertCreated();
    $expenseId = $response->json('id');

    $this->assertDatabaseHas('expenses', [
        'id' => $expenseId,
        'created_at' => $backdated->toDateTimeString(),
    ]);
});

it('rejects a bare date created_at without an explicit offset', function () {
    $budget = Budget::factory()->create();
    $account = Account::factory()->create();

    $data = [
        'amount' => 20.00,
        'description' => 'Coffee',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
        'created_at' => '2026-06-29',
    ];

    $this->postJson('/api/expenses', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['created_at']);
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

    expect($expense->fresh()->deleted_at)->not->toBeNull();
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

it('subtracts the expense amount from the selected account on create', function () {
    $budget = Budget::factory()->create();
    $account = Account::factory()->create(['amount' => 1000]);

    $this->postJson('/api/expenses', [
        'amount' => 250.50,
        'description' => 'Groceries',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
    ])->assertCreated();

    expect($account->refresh()->amount)->toBe('749.50');
});

it('subtracts the expense amount from the account only once when client_id is duplicated', function () {
    $budget = Budget::factory()->create();
    $account = Account::factory()->create(['amount' => 1000]);

    $data = [
        'amount' => 100,
        'description' => 'Groceries',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
        'client_id' => (string) Str::uuid(),
    ];

    $this->postJson('/api/expenses', $data)->assertCreated();
    $this->postJson('/api/expenses', $data)->assertCreated();

    expect($account->refresh()->amount)->toBe('900.00');
});

it('adjusts the account by the difference when the expense amount is updated', function () {
    $budget = Budget::factory()->create();
    $account = Account::factory()->create(['amount' => 1000]);

    $expenseId = $this->postJson('/api/expenses', [
        'amount' => 100,
        'description' => 'Groceries',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
    ])->json('id');

    $this->putJson("/api/expenses/{$expenseId}", [
        'amount' => 300,
        'description' => 'Groceries',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
    ])->assertOk();

    expect($account->refresh()->amount)->toBe('700.00');
});

it('moves the expense amount between accounts when the account is changed on update', function () {
    $budget = Budget::factory()->create();
    $oldAccount = Account::factory()->create(['amount' => 1000]);
    $newAccount = Account::factory()->create(['amount' => 500]);

    $expenseId = $this->postJson('/api/expenses', [
        'amount' => 100,
        'description' => 'Groceries',
        'budget_id' => $budget->id,
        'account_id' => $oldAccount->id,
    ])->json('id');

    $this->putJson("/api/expenses/{$expenseId}", [
        'amount' => 150,
        'description' => 'Groceries',
        'budget_id' => $budget->id,
        'account_id' => $newAccount->id,
    ])->assertOk();

    expect($oldAccount->refresh()->amount)->toBe('1000.00')
        ->and($newAccount->refresh()->amount)->toBe('350.00');
});

it('gives the expense amount back to the account on delete', function () {
    $budget = Budget::factory()->create();
    $account = Account::factory()->create(['amount' => 1000]);

    $expenseId = $this->postJson('/api/expenses', [
        'amount' => 100,
        'description' => 'Groceries',
        'budget_id' => $budget->id,
        'account_id' => $account->id,
    ])->json('id');

    $this->deleteJson("/api/expenses/{$expenseId}")->assertNoContent();

    expect($account->refresh()->amount)->toBe('1000.00');
});

it('keeps the expense row in the database when it is soft deleted', function () {
    $expense = Expense::factory()->create();

    $this->deleteJson("/api/expenses/{$expense->id}")->assertNoContent();

    $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    expect(Expense::query()->find($expense->id))->toBeNull()
        ->and(Expense::withTrashed()->find($expense->id))->not->toBeNull();
});

it('excludes soft deleted expenses from the list', function () {
    $kept = Expense::factory()->create();
    Expense::factory()->create()->delete();

    $response = $this->getJson('/api/expenses')
        ->assertOk()
        ->assertJsonCount(1);

    expect($response->json('0.id'))->toBe($kept->id);
});

it('soft deletes the balance record when its expense is deleted', function () {
    $expenseId = $this->postJson('/api/expenses', [
        'amount' => 100,
        'description' => 'Groceries',
        'budget_id' => Budget::factory()->create()->id,
        'account_id' => Account::factory()->create()->id,
    ])->json('id');

    $balance = Expense::query()->findOrFail($expenseId)->balance;

    $this->deleteJson("/api/expenses/{$expenseId}")->assertNoContent();

    $this->assertSoftDeleted('expenses', ['id' => $expenseId]);
    $this->assertSoftDeleted('balances', ['id' => $balance->id]);
});

it('returns 404 when updating or deleting a soft deleted expense', function () {
    $expense = Expense::factory()->create();
    $expense->delete();

    $this->putJson("/api/expenses/{$expense->id}", [
        'amount' => 100.00,
        'description' => 'Test',
        'budget_id' => $expense->budget_id,
        'account_id' => $expense->account_id,
    ])->assertNotFound();

    $this->deleteJson("/api/expenses/{$expense->id}")->assertNotFound();
});

it('does not give the expense amount back twice when delete is repeated', function () {
    $account = Account::factory()->create(['amount' => 1000]);

    $expenseId = $this->postJson('/api/expenses', [
        'amount' => 100,
        'description' => 'Groceries',
        'budget_id' => Budget::factory()->create()->id,
        'account_id' => $account->id,
    ])->json('id');

    $this->deleteJson("/api/expenses/{$expenseId}")->assertNoContent();
    $this->deleteJson("/api/expenses/{$expenseId}")->assertNotFound();

    expect($account->refresh()->amount)->toBe('1000.00');
});
