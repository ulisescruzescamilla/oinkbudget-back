<?php

use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns all budgets', function () {
    Budget::factory()->count(3)->create();

    $this->getJson('/api/budgets')
        ->assertOk()
        ->assertJsonCount(3);
});

it('creates a budget', function () {
    $data = [
        'max_limit' => 1000.00,
        'expense_amount' => 200.00,
        'percentage_value' => 20,
        'graph_color' => 'FF5733',
    ];

    $this->postJson('/api/budgets', $data)
        ->assertCreated()
        ->assertJsonFragment(['graph_color' => 'FF5733']);

    $this->assertDatabaseHas('budgets', ['graph_color' => 'FF5733']);
});

it('validates required fields on store', function () {
    $this->postJson('/api/budgets', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['max_limit', 'expense_amount', 'percentage_value', 'graph_color']);
});

it('validates graph_color is a 6-char hex string', function () {
    $data = Budget::factory()->make(['graph_color' => 'ZZZZZZ'])->toArray();

    $this->postJson('/api/budgets', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['graph_color']);
});

it('validates percentage_value is between 0 and 100', function () {
    $data = Budget::factory()->make(['percentage_value' => 150])->toArray();

    $this->postJson('/api/budgets', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['percentage_value']);
});

it('updates a budget', function () {
    $budget = Budget::factory()->create();

    $data = [
        'max_limit' => 2000.00,
        'expense_amount' => 500.00,
        'percentage_value' => 25,
        'graph_color' => 'AABBCC',
    ];

    $this->putJson("/api/budgets/{$budget->id}", $data)
        ->assertOk()
        ->assertJsonFragment(['graph_color' => 'AABBCC']);

    $this->assertDatabaseHas('budgets', ['id' => $budget->id, 'graph_color' => 'AABBCC']);
});

it('deletes a budget', function () {
    $budget = Budget::factory()->create();

    $this->deleteJson("/api/budgets/{$budget->id}")->assertNoContent();

    $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
});

it('returns 404 when updating a non-existent budget', function () {
    $data = [
        'max_limit' => 1000.00,
        'expense_amount' => 200.00,
        'percentage_value' => 20,
        'graph_color' => 'FF5733',
    ];

    $this->putJson('/api/budgets/999', $data)->assertNotFound();
});

it('returns 404 when deleting a non-existent budget', function () {
    $this->deleteJson('/api/budgets/999')->assertNotFound();
});
