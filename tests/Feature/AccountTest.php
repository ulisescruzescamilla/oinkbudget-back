<?php

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
