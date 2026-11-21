<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

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

it('returns the existing budget when client_id is duplicated', function () {
    $data = Budget::factory()->make()->toArray();
    $data['client_id'] = (string) Str::uuid();

    $firstResponse = $this->postJson('/api/budgets', $data)->assertCreated();
    $secondResponse = $this->postJson('/api/budgets', $data)->assertCreated();

    $firstId = json_decode($firstResponse->getContent(), true)['id'];
    $secondId = json_decode($secondResponse->getContent(), true)['id'];

    expect($secondId)->toBe($firstId);
    $this->assertDatabaseCount('budgets', 1);
});

it('validates required fields on store', function () {
    $this->postJson('/api/budgets', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['max_limit', 'expense_amount', 'percentage_value']);
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
    $data = Budget::factory()->make(['category_id' => null])->toArray();

    $this->postJson('/api/budgets', $data)
        ->assertCreated();
});

it('updates a budget category', function () {
    $budget = Budget::factory()->create();
    $category = Category::factory()->create();

    $data = [
        'name' => $budget->name,
        'max_limit' => $budget->max_limit,
        'category_id' => $category->id,
    ];

    $this->putJson("/api/budgets/{$budget->id}", $data)
        ->assertOk();

    $this->assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'category_id' => $category->id,
    ]);
});

it('creates a budget within a period', function () {
    $period = Period::factory()->create();
    $data = Budget::factory()->make(['period_id' => $period->id])->toArray();

    $response = $this->postJson('/api/budgets', $data)
        ->assertCreated()
        ->assertJsonPath('period_id', $period->id);

    $this->assertDatabaseHas('budgets', [
        'id' => $response->json('id'),
        'period_id' => $period->id,
    ]);
});

it('validates period_id must exist in periods table', function () {
    $data = Budget::factory()->make(['period_id' => 9999])->toArray();

    $this->postJson('/api/budgets', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['period_id']);
});

it('updates a budget period', function () {
    $budget = Budget::factory()->create();
    $period = Period::factory()->create();

    $this->putJson("/api/budgets/{$budget->id}", [
        'max_limit' => $budget->max_limit,
        'period_id' => $period->id,
    ])->assertOk();

    $this->assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'period_id' => $period->id,
    ]);
});

it('returns only the budgets of the given period', function () {
    $period = Period::factory()->create();
    Budget::factory()->count(2)->create(['period_id' => $period->id]);
    Budget::factory()->create(['period_id' => Period::factory()]);
    Budget::factory()->create(['period_id' => null]);

    $this->getJson("/api/budgets?period_id={$period->id}")
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonPath('0.period_id', $period->id)
        ->assertJsonPath('1.period_id', $period->id);
});

it('validates period_id must exist when filtering budgets', function () {
    $this->getJson('/api/budgets?period_id=9999')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['period_id']);
});
