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
