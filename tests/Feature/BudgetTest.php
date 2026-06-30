<?php

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns all budgets', function () {
    Budget::factory()->count(3)->create();

    $this->getJson('/api/budgets')
        ->assertOk()
        ->assertJsonCount(3);
});

it('creates a budget', function () {
    $data = Budget::factory()->make()->toArray();

    $response = $this->postJson('/api/budgets', $data)
        ->assertCreated();

    $budget = json_decode($response->getContent(), true);

    $this->assertDatabaseHas('budgets', [
        'id' => $budget['id'],
        'max_limit' => $budget['max_limit'],
    ]);
});

it('validates required fields on store', function () {
    $this->postJson('/api/budgets', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['max_limit', 'expense_amount', 'percentage_value', 'start_date', 'end_date']);
});

it('validates percentage_value is between 0 and 100', function () {
    $data = Budget::factory()->make(['percentage_value' => 150])->toArray();

    $this->postJson('/api/budgets', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['percentage_value']);
});

it('validates end_date is after start_date', function () {
    $data = Budget::factory()->make([
        'start_date' => '2026-06-01',
        'end_date' => '2026-05-01',
    ])->toArray();

    $this->postJson('/api/budgets', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['end_date']);
});

it('updates a budget', function () {
    $budget = Budget::factory()->create();

    $data = [
        'max_limit' => 2000.00,
        'expense_amount' => 500.00,
        'percentage_value' => 25,
        'start_date' => '2026-02-01',
        'end_date' => '2026-02-28',
    ];

    $this->putJson("/api/budgets/{$budget->id}", $data)
        ->assertOk();

    $this->assertDatabaseHas('budgets', ['id' => $budget->id]);
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
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
    ];

    $this->putJson('/api/budgets/999', $data)->assertNotFound();
});

it('returns 404 when deleting a non-existent budget', function () {
    $this->deleteJson('/api/budgets/999')->assertNotFound();
});

it('creates a budget with a category', function () {
    $category = Category::factory()->create();
    $data = Budget::factory()->make(['category_id' => $category->id])->toArray();

    $response = $this->postJson('/api/budgets', $data)
        ->assertCreated();

    $budget = json_decode($response->getContent(), true);

    $this->assertDatabaseHas('budgets', [
        'id' => $budget['id'],
        'category_id' => $category->id,
    ]);
});

it('validates category_id must exist in categories table', function () {
    $data = Budget::factory()->make(['category_id' => 9999])->toArray();

    $this->postJson('/api/budgets', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

it('creates a budget without a category', function () {
    $data = Budget::factory()->make([
        'category_id' => null,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
    ])->toArray();

    $this->postJson('/api/budgets', $data)
        ->assertCreated();
});

it('updates a budget category', function () {
    $budget = Budget::factory()->create();
    $category = Category::factory()->create();

    $data = [
        'name' => $budget->name,
        'max_limit' => $budget->max_limit,
        'period' => $budget->period,
        'start_date' => $budget->start_date->format('Y-m-d'),
        'end_date' => $budget->end_date->format('Y-m-d'),
        'category_id' => $category->id,
    ];

    $this->putJson("/api/budgets/{$budget->id}", $data)
        ->assertOk();

    $this->assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'category_id' => $category->id,
    ]);
});
