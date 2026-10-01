<?php

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('creates an account', function () {
    $data = Account::factory()->make()->toArray();

    $this->postJson('/api/accounts', $data)
        ->assertCreated()
        ->assertJsonFragment(['name' => $data['name']]);

    $this->assertDatabaseHas('accounts', ['name' => $data['name']]);
});

it('returns the existing account when client_id is duplicated', function () {
    $data = Account::factory()->make()->toArray();
    $data['client_id'] = (string) Str::uuid();

    $firstResponse = $this->postJson('/api/accounts', $data)->assertCreated();
    $secondResponse = $this->postJson('/api/accounts', $data)->assertCreated();

    $firstId = json_decode($firstResponse->getContent(), true)['id'];
    $secondId = json_decode($secondResponse->getContent(), true)['id'];

    expect($secondId)->toBe($firstId);
    $this->assertDatabaseCount('accounts', 1);
});

it('soft deletes an account', function () {
    $account = Account::factory()->create();

    $this->deleteJson("/api/accounts/{$account->id}")->assertNoContent();

    $this->assertSoftDeleted('accounts', ['id' => $account->id]);
});

it('excludes soft deleted accounts from normal queries', function () {
    $account = Account::factory()->create();
    $account->delete();

    expect(Account::query()->find($account->id))->toBeNull();
});

it('restores a soft deleted account', function () {
    $account = Account::factory()->create();
    $account->delete();

    $this->postJson("/api/accounts/{$account->id}/restore")->assertSuccessful();

    $this->assertNotSoftDeleted('accounts', ['id' => $account->id]);
});

it('returns the restored account in the response', function () {
    $account = Account::factory()->create();
    $account->delete();

    $this->postJson("/api/accounts/{$account->id}/restore")
        ->assertSuccessful()
        ->assertJsonFragment(['id' => $account->id]);
});

it('returns 404 when restoring a non-existent account', function () {
    $this->postJson('/api/accounts/999/restore')->assertNotFound();
});

it('transfers an amount between two accounts', function () {
    $from = Account::factory()->create(['amount' => 500]);
    $to = Account::factory()->create(['amount' => 100]);

    $this->postJson('/api/accounts/transfer', [
        'account_from' => $from->id,
        'account_to' => $to->id,
        'amount' => 150.5,
    ])->assertOk();

    $this->assertDatabaseHas('accounts', ['id' => $from->id, 'amount' => 349.5]);
    $this->assertDatabaseHas('accounts', ['id' => $to->id, 'amount' => 250.5]);
});

it('rejects a transfer with missing fields', function () {
    $this->postJson('/api/accounts/transfer', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['account_from', 'account_to', 'amount']);
});

it('rejects a transfer to the same account', function () {
    $account = Account::factory()->create(['amount' => 500]);

    $this->postJson('/api/accounts/transfer', [
        'account_from' => $account->id,
        'account_to' => $account->id,
        'amount' => 50,
    ])->assertUnprocessable()->assertJsonValidationErrors(['account_to']);

    $this->assertDatabaseHas('accounts', ['id' => $account->id, 'amount' => 500]);
});

it('rejects a transfer to an account that does not exist', function () {
    $from = Account::factory()->create(['amount' => 500]);

    $this->postJson('/api/accounts/transfer', [
        'account_from' => $from->id,
        'account_to' => 999,
        'amount' => 50,
    ])->assertUnprocessable()->assertJsonValidationErrors(['account_to']);
});
