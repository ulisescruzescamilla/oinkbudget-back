<?php

use App\Models\Account;
use App\Models\Income;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
