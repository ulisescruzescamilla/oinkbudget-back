<?php

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns all categories', function () {
    Category::factory()->count(3)->create();

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonCount(3);
});

it('creates a category', function () {
    $this->postJson('/api/categories', [
        'name' => 'Mascotas',
        'type' => 'expense',
        'color' => '#1289E7',
        'icon_code' => 'paw',
    ])
        ->assertCreated()
        ->assertJsonPath('name', 'Mascotas')
        ->assertJsonPath('type', 'expense')
        ->assertJsonPath('color', '#1289E7')
        ->assertJsonPath('icon_code', 'paw');

    $this->assertDatabaseHas('categories', [
        'name' => 'Mascotas',
        'type' => 'expense',
        'color' => '#1289E7',
        'icon_code' => 'paw',
    ]);
});

it('trims the category name', function () {
    $this->postJson('/api/categories', ['name' => '  Mascotas ', 'type' => 'income'])
        ->assertCreated()
        ->assertJsonPath('name', 'Mascotas');
});

it('applies the default color and icon when omitted', function () {
    $this->postJson('/api/categories', ['name' => 'Mascotas', 'type' => 'expense'])
        ->assertCreated()
        ->assertJsonPath('color', '#8C6DE2')
        ->assertJsonPath('icon_code', 'tag');
});

it('validates required fields on store', function () {
    $this->postJson('/api/categories', ['name' => '   '])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name' => 'El nombre es obligatorio.',
            'type' => 'El tipo es obligatorio.',
        ]);
});

it('creates a category for each type', function (string $type) {
    $this->postJson('/api/categories', ['name' => 'Mascotas', 'type' => $type])
        ->assertCreated()
        ->assertJsonPath('type', $type);
})->with(['income', 'expense']);

it('validates the category fields', function (array $payload, string $field) {
    $this->postJson('/api/categories', ['name' => 'Mascotas', 'type' => 'expense', ...$payload])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$field]);
})->with([
    'name too long' => [['name' => str_repeat('a', 256)], 'name'],
    'color without hash' => [['color' => '1289E7'], 'color'],
    'color too short' => [['color' => '#1289E'], 'color'],
    'color not hex' => [['color' => '#GGGGGG'], 'color'],
    'icon_code too long' => [['icon_code' => str_repeat('a', 11)], 'icon_code'],
    'unknown type' => [['type' => 'transfer'], 'type'],
    'type not a string' => [['type' => ['income']], 'type'],
]);

it('rejects a duplicated name on store', function (string $name) {
    Category::factory()->create(['name' => 'Mascotas']);

    $this->postJson('/api/categories', ['name' => $name])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name' => 'Ya existe una categoría con ese nombre.']);
})->with([
    'same name' => ['Mascotas'],
    'surrounded by spaces' => ['  Mascotas '],
    'different case' => ['mascotas'],
]);

it('allows reusing the name of a soft deleted category', function () {
    Category::factory()->create(['name' => 'Mascotas'])->delete();

    $this->postJson('/api/categories', ['name' => 'Mascotas', 'type' => 'expense'])
        ->assertCreated();
});

it('rejects a duplicated name on update', function () {
    Category::factory()->create(['name' => 'Mascotas']);
    $category = Category::factory()->create(['name' => 'Hogar']);

    $this->putJson("/api/categories/{$category->id}", ['name' => ' Mascotas '])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name' => 'Ya existe una categoría con ese nombre.']);
});

it('rejects a duplicated name in a different case on update', function () {
    Category::factory()->create(['name' => 'Mascotas']);
    $category = Category::factory()->create(['name' => 'Hogar']);

    $this->putJson("/api/categories/{$category->id}", ['name' => 'MASCOTAS'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name' => 'Ya existe una categoría con ese nombre.']);
});

it('allows changing the case of a category own name', function () {
    $category = Category::factory()->create(['name' => 'Mascotas']);

    $this->putJson("/api/categories/{$category->id}", ['name' => 'MASCOTAS'])
        ->assertOk()
        ->assertJsonPath('name', 'MASCOTAS');
});

it('allows updating a category with its own name', function () {
    $category = Category::factory()->create(['name' => 'Mascotas']);

    $this->putJson("/api/categories/{$category->id}", ['name' => 'Mascotas', 'color' => '#1289E7'])
        ->assertOk()
        ->assertJsonPath('color', '#1289E7');
});

it('updates a category', function () {
    $category = Category::factory()->create(['name' => 'Mascotas', 'type' => 'expense', 'color' => '#1289E7']);

    $this->putJson("/api/categories/{$category->id}", ['name' => ' Hogar ', 'icon_code' => 'briefcase'])
        ->assertOk()
        ->assertJsonPath('name', 'Hogar')
        ->assertJsonPath('type', 'expense')
        ->assertJsonPath('color', '#1289E7')
        ->assertJsonPath('icon_code', 'briefcase');
});

it('rejects a blank name on update', function () {
    $category = Category::factory()->create();

    $this->putJson("/api/categories/{$category->id}", ['name' => '   '])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name' => 'El nombre es obligatorio.']);
});

it('validates fields on update', function () {
    $category = Category::factory()->create();

    $this->putJson("/api/categories/{$category->id}", ['color' => 'blue', 'type' => 'transfer'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['color', 'type' => 'El tipo debe ser ingreso o gasto.']);
});

it('updates the category type', function () {
    $category = Category::factory()->create(['type' => 'expense']);

    $this->putJson("/api/categories/{$category->id}", ['type' => 'income'])
        ->assertOk()
        ->assertJsonPath('type', 'income');

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'type' => 'income']);
});

it('returns not found when updating a missing category', function () {
    $this->putJson('/api/categories/999', ['name' => 'Mascotas'])
        ->assertNotFound();
});

it('permanently deletes a category without budgets', function () {
    $category = Category::factory()->create();

    $this->deleteJson("/api/categories/{$category->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonCount(0);
});

it('soft deletes a category that belongs to a budget', function () {
    $category = Category::factory()->create();
    $budget = Budget::factory()->create(['category_id' => $category->id]);

    $this->deleteJson("/api/categories/{$category->id}")
        ->assertNoContent();

    $this->assertSoftDeleted('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('budgets', ['id' => $budget->id, 'category_id' => $category->id]);
});
