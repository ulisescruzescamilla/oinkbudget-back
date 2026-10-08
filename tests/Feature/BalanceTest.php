<?php

use App\Models\Account;
use App\Models\Balance;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns only today balances by default', function () {
    Balance::factory()->count(3)->create(['created_at' => Carbon::today()]);
    Balance::factory()->count(2)->create(['created_at' => Carbon::yesterday()]);

    $this->getJson('/api/balances')
        ->assertOk()
        ->assertJsonCount(3);
});

it('filters balances by date range', function () {
    Balance::factory()->create(['created_at' => '2026-06-01']);
    Balance::factory()->create(['created_at' => '2026-06-15']);
    Balance::factory()->create(['created_at' => '2026-07-01']);

    $this->getJson('/api/balances?start_date=2026-06-01&end_date=2026-06-30')
        ->assertOk()
        ->assertJsonCount(2);
});

it('returns balances ordered by created_at desc by default, as unshifted UTC ISO-8601', function () {
    Balance::factory()->create(['created_at' => '2026-06-01']);
    Balance::factory()->create(['created_at' => '2026-06-02']);

    $response = $this->getJson('/api/balances?start_date=2026-06-01&end_date=2026-06-30')
        ->assertOk();

    $data = $response->json();

    expect($data[0]['created_at'])->toBe(Carbon::parse('2026-06-02')->toJSON());
    expect($data[1]['created_at'])->toBe(Carbon::parse('2026-06-01')->toJSON());
});

it('returns balances ordered by created_at asc', function () {
    Balance::factory()->create(['created_at' => '2026-06-01']);
    Balance::factory()->create(['created_at' => '2026-06-02']);

    $response = $this->getJson('/api/balances?start_date=2026-06-01&end_date=2026-06-30&order=asc')
        ->assertOk();

    $data = $response->json();

    expect($data[0]['created_at'])->toBe(Carbon::parse('2026-06-01')->toJSON());
    expect($data[1]['created_at'])->toBe(Carbon::parse('2026-06-02')->toJSON());
});

it('groups a Mexico-City-evening transaction under the next UTC calendar day', function () {
    // 2026-06-01 22:00 America/Mexico_City (UTC-6) is 2026-06-02 04:00 UTC.
    $localEvening = Carbon::parse('2026-06-01 22:00:00', 'America/Mexico_City');

    Balance::factory()->create(['created_at' => $localEvening->copy()->utc()]);

    $response = $this->getJson('/api/balances?start_date=2026-06-02&end_date=2026-06-02')
        ->assertOk()
        ->assertJsonCount(1);

    expect($response->json('0.created_at'))->toBe($localEvening->copy()->utc()->toJSON());
});

it('validates date format for date filters', function () {
    $this->getJson('/api/balances?start_date=01-06-2026&end_date=30-06-2026')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date', 'end_date']);
});

it('validates order parameter', function () {
    $this->getJson('/api/balances?order=invalid')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['order']);
});

it('filters balances by range=week', function () {
    Balance::factory()->create(['created_at' => Carbon::today()]);
    Balance::factory()->create(['created_at' => Carbon::today()->subDays(6)]);
    Balance::factory()->create(['created_at' => Carbon::today()->subDays(7)]);

    $this->getJson('/api/balances?range=week')
        ->assertOk()
        ->assertJsonCount(2);
});

it('filters balances by range=month', function () {
    Balance::factory()->create(['created_at' => Carbon::today()]);
    Balance::factory()->create(['created_at' => Carbon::today()->subDays(29)]);
    Balance::factory()->create(['created_at' => Carbon::today()->subDays(30)]);

    $this->getJson('/api/balances?range=month')
        ->assertOk()
        ->assertJsonCount(2);
});

it('returns every balance when range=all', function () {
    Balance::factory()->create(['created_at' => Carbon::today()]);
    Balance::factory()->create(['created_at' => Carbon::today()->subYear()]);

    $this->getJson('/api/balances?range=all')
        ->assertOk()
        ->assertJsonCount(2);
});

it('filters balances by type=income', function () {
    Balance::factory()->create(['type' => 'income', 'created_at' => Carbon::today()]);
    Balance::factory()->create(['type' => 'expense', 'created_at' => Carbon::today()]);

    $response = $this->getJson('/api/balances?type=income')
        ->assertOk()
        ->assertJsonCount(1);

    expect($response->json('0.type'))->toBe('income');
});

it('filters balances by type=expense', function () {
    Balance::factory()->create(['type' => 'income', 'created_at' => Carbon::today()]);
    Balance::factory()->create(['type' => 'expense', 'created_at' => Carbon::today()]);

    $response = $this->getJson('/api/balances?type=expense')
        ->assertOk()
        ->assertJsonCount(1);

    expect($response->json('0.type'))->toBe('expense');
});

it('combines range and type filters', function () {
    Balance::factory()->create(['type' => 'income', 'created_at' => Carbon::today()]);
    Balance::factory()->create(['type' => 'expense', 'created_at' => Carbon::today()]);
    Balance::factory()->create(['type' => 'income', 'created_at' => Carbon::today()->subDays(10)]);

    $this->getJson('/api/balances?range=week&type=income')
        ->assertOk()
        ->assertJsonCount(1);
});

it('validates range parameter', function () {
    $this->getJson('/api/balances?range=invalid')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['range']);
});

it('validates type parameter', function () {
    $this->getJson('/api/balances?type=invalid')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

it('includes account relationship', function () {
    $balance = Balance::factory()->create(['created_at' => Carbon::today()]);

    $response = $this->getJson('/api/balances')
        ->assertOk();

    $data = $response->json();

    expect($data[0])->toHaveKey('account');
});

it('soft deletes a balance', function () {
    $balance = Balance::factory()->create();

    $this->deleteJson("/api/balances/{$balance->id}")->assertSuccessful();

    $this->assertSoftDeleted('balances', ['id' => $balance->id]);
    expect(Balance::query()->find($balance->id))->toBeNull()
        ->and(Balance::withTrashed()->find($balance->id))->not->toBeNull();
});

it('returns 404 when deleting a non-existent balance', function () {
    $this->deleteJson('/api/balances/999')->assertNotFound();
});

it('returns 404 when deleting an already soft deleted balance', function () {
    $balance = Balance::factory()->create();
    $balance->delete();

    $this->deleteJson("/api/balances/{$balance->id}")->assertNotFound();
});

it('excludes soft deleted balances from the list', function () {
    $kept = Balance::factory()->create(['created_at' => Carbon::today()]);
    Balance::factory()->create(['created_at' => Carbon::today()])->delete();

    $response = $this->getJson('/api/balances')
        ->assertOk()
        ->assertJsonCount(1);

    expect($response->json('0.id'))->toBe($kept->id);
});

it('excludes soft deleted balances from date and range filters', function (string $query) {
    Balance::factory()->create(['created_at' => Carbon::today()]);
    Balance::factory()->create(['created_at' => Carbon::today()])->delete();

    $this->getJson("/api/balances?{$query}")
        ->assertOk()
        ->assertJsonCount(1);
})->with([
    'range=all' => 'range=all',
    'range=week' => 'range=week',
    'date range' => fn () => 'start_date='.Carbon::today()->toDateString().'&end_date='.Carbon::today()->toDateString(),
]);

it('soft deletes the related expense when its balance is deleted', function () {
    $expenseId = $this->postJson('/api/expenses', [
        'amount' => 100,
        'description' => 'Groceries',
        'budget_id' => Budget::factory()->create()->id,
        'account_id' => Account::factory()->create()->id,
    ])->json('id');

    $balance = Expense::query()->findOrFail($expenseId)->balance;

    $this->deleteJson("/api/balances/{$balance->id}")->assertSuccessful();

    $this->assertSoftDeleted('balances', ['id' => $balance->id]);
    $this->assertSoftDeleted('expenses', ['id' => $expenseId]);
});

it('soft deletes the related income when its balance is deleted', function () {
    $incomeId = $this->postJson('/api/incomes', [
        'amount' => 100,
        'description' => 'Freelance',
        'account_id' => Account::factory()->create()->id,
    ])->json('id');

    $balance = Income::query()->findOrFail($incomeId)->balance;

    $this->deleteJson("/api/balances/{$balance->id}")->assertSuccessful();

    $this->assertSoftDeleted('balances', ['id' => $balance->id]);
    $this->assertSoftDeleted('incomes', ['id' => $incomeId]);
});

it('only soft deletes the income or expense that belongs to the deleted balance', function () {
    $account = Account::factory()->create();

    $deletedId = $this->postJson('/api/incomes', [
        'amount' => 100,
        'description' => 'Freelance',
        'account_id' => $account->id,
    ])->json('id');
    $keptId = $this->postJson('/api/incomes', [
        'amount' => 50,
        'description' => 'Salary',
        'account_id' => $account->id,
    ])->json('id');

    $balance = Income::query()->findOrFail($deletedId)->balance;

    $this->deleteJson("/api/balances/{$balance->id}")->assertSuccessful();

    $this->assertSoftDeleted('incomes', ['id' => $deletedId]);
    $this->assertNotSoftDeleted('incomes', ['id' => $keptId]);
    expect(Income::query()->findOrFail($keptId)->balance)->not->toBeNull();
});
