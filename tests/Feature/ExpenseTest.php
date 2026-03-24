<?php

use App\Models\Account;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
