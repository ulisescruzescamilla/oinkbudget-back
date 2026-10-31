<?php

use App\Models\RecurrentBudget;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns all recurrent budgets', function () {
    RecurrentBudget::factory()->count(3)->create();

    $this->getJson('/api/recurrent-budgets')
        ->assertOk()
        ->assertJsonCount(3);
});

it('creates a recurrent budget', function () {
    $this->postJson('/api/recurrent-budgets', [
        'name' => 'Renta',
        'color' => '#1289E7',
        'icon_code' => 'home',
    ])
        ->assertCreated()
        ->assertJsonPath('name', 'Renta')
        ->assertJsonPath('color', '#1289E7')
        ->assertJsonPath('icon_code', 'home');

    $this->assertDatabaseHas('recurrent_budgets', [
        'name' => 'Renta',
        'color' => '#1289E7',
        'icon_code' => 'home',
    ]);
});

it('trims the recurrent budget name', function () {
    $this->postJson('/api/recurrent-budgets', ['name' => '  Renta '])
        ->assertCreated()
        ->assertJsonPath('name', 'Renta');
});

it('applies the default color and icon when omitted', function () {
    $this->postJson('/api/recurrent-budgets', ['name' => 'Renta'])
        ->assertCreated()
        ->assertJsonPath('color', '#8C6DE2')
        ->assertJsonPath('icon_code', 'tag');
});

it('validates the name is required on store', function () {
    $this->postJson('/api/recurrent-budgets', ['name' => '   '])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name' => 'El nombre es obligatorio.']);
});

it('validates the recurrent budget fields', function (array $payload, string $field) {
    $this->postJson('/api/recurrent-budgets', ['name' => 'Renta', ...$payload])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$field]);
})->with([
    'name too long' => [['name' => str_repeat('a', 256)], 'name'],
    'name not a string' => [['name' => ['Renta']], 'name'],
    'color without hash' => [['color' => '1289E7'], 'color'],
    'color too short' => [['color' => '#1289E'], 'color'],
    'color not hex' => [['color' => '#GGGGGG'], 'color'],
    'icon_code too long' => [['icon_code' => str_repeat('a', 11)], 'icon_code'],
]);

it('rejects a duplicated name on store', function (string $name) {
    RecurrentBudget::factory()->create(['name' => 'Renta']);

    $this->postJson('/api/recurrent-budgets', ['name' => $name])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name' => 'Ya existe un presupuesto recurrente con ese nombre.']);
})->with([
    'same name' => ['Renta'],
    'surrounded by spaces' => ['  Renta '],
    'different case' => ['renta'],
]);

it('allows reusing the name of a soft deleted recurrent budget', function () {
    RecurrentBudget::factory()->create(['name' => 'Renta'])->delete();

    $this->postJson('/api/recurrent-budgets', ['name' => 'Renta'])
        ->assertCreated();
});

it('updates a recurrent budget', function () {
    $recurrentBudget = RecurrentBudget::factory()->create(['name' => 'Renta', 'color' => '#1289E7']);

    $this->putJson("/api/recurrent-budgets/{$recurrentBudget->id}", ['name' => ' Internet ', 'icon_code' => 'wifi'])
        ->assertOk()
        ->assertJsonPath('name', 'Internet')
        ->assertJsonPath('color', '#1289E7')
        ->assertJsonPath('icon_code', 'wifi');
});

it('rejects a duplicated name in a different case on update', function () {
    RecurrentBudget::factory()->create(['name' => 'Renta']);
    $recurrentBudget = RecurrentBudget::factory()->create(['name' => 'Internet']);

    $this->putJson("/api/recurrent-budgets/{$recurrentBudget->id}", ['name' => 'RENTA'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name' => 'Ya existe un presupuesto recurrente con ese nombre.']);
});

it('allows changing the case of a recurrent budget own name', function () {
    $recurrentBudget = RecurrentBudget::factory()->create(['name' => 'Renta']);

    $this->putJson("/api/recurrent-budgets/{$recurrentBudget->id}", ['name' => 'RENTA'])
        ->assertOk()
        ->assertJsonPath('name', 'RENTA');
});

it('allows updating a recurrent budget with its own name', function () {
    $recurrentBudget = RecurrentBudget::factory()->create(['name' => 'Renta']);

    $this->putJson("/api/recurrent-budgets/{$recurrentBudget->id}", ['name' => 'Renta', 'color' => '#1289E7'])
        ->assertOk()
        ->assertJsonPath('color', '#1289E7');
});

it('rejects a duplicated name on update', function () {
    RecurrentBudget::factory()->create(['name' => 'Renta']);
    $recurrentBudget = RecurrentBudget::factory()->create(['name' => 'Internet']);

    $this->putJson("/api/recurrent-budgets/{$recurrentBudget->id}", ['name' => ' Renta '])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name' => 'Ya existe un presupuesto recurrente con ese nombre.']);
});

it('rejects a blank name on update', function () {
    $recurrentBudget = RecurrentBudget::factory()->create();

    $this->putJson("/api/recurrent-budgets/{$recurrentBudget->id}", ['name' => '   '])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name' => 'El nombre es obligatorio.']);
});

it('validates fields on update', function () {
    $recurrentBudget = RecurrentBudget::factory()->create();

    $this->putJson("/api/recurrent-budgets/{$recurrentBudget->id}", ['color' => 'blue', 'icon_code' => str_repeat('a', 11)])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['color', 'icon_code']);
});

it('returns not found when updating a missing recurrent budget', function () {
    $this->putJson('/api/recurrent-budgets/999', ['name' => 'Renta'])
        ->assertNotFound();
});

it('soft deletes a recurrent budget', function () {
    $recurrentBudget = RecurrentBudget::factory()->create();

    $this->deleteJson("/api/recurrent-budgets/{$recurrentBudget->id}")
        ->assertNoContent();

    $this->assertSoftDeleted('recurrent_budgets', ['id' => $recurrentBudget->id]);

    $this->getJson('/api/recurrent-budgets')
        ->assertOk()
        ->assertJsonCount(0);
});

it('returns not found when deleting a missing recurrent budget', function () {
    $this->deleteJson('/api/recurrent-budgets/999')
        ->assertNotFound();
});
