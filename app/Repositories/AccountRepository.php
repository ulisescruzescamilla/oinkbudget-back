<?php

namespace App\Repositories;

use App\DataTransferObjects\AccountData;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountRepository
{
    public function get()
    {
        return Account::query()->get();
    }

    public function transfer(Account $accountFrom, Account $accountTo, float $amount): void
    {
        DB::transaction(function () use ($accountFrom, $accountTo, $amount) {
            $accountFrom->amount = $accountFrom->amount - $amount;
            $accountTo->amount = $accountTo->amount + $amount;

            $accountFrom->save();
            $accountTo->save();
        });
    }

    /**
     * Add the given amount to the account, including soft-deleted accounts
     * so their amount is still correct if they are restored.
     */
    public function deposit(int $accountId, float $amount): void
    {
        Account::query()->withTrashed()->whereKey($accountId)->increment('amount', $amount);
    }

    /**
     * Subtract the given amount from the account, including soft-deleted accounts
     * so their amount is still correct if they are restored.
     */
    public function withdraw(int $accountId, float $amount): void
    {
        Account::query()->withTrashed()->whereKey($accountId)->decrement('amount', $amount);
    }

    public function store(AccountData $data): Account
    {
        if ($data->client_id) {
            $existing = Account::query()->where('client_id', $data->client_id)->first();

            if ($existing) {
                return $existing;
            }
        }

        return Account::query()->create($data->toArray());
    }

    public function update(Account $account, AccountData $data): Account
    {
        $account->update($data->toArray());

        return $account->fresh();
    }

    public function delete(Account $account): void
    {
        $account->delete();
    }

    public function restore(Account $account): Account
    {
        $account->restore();

        return $account->fresh();
    }

    public function amountAvailable(): float
    {
        return Account::query()
            ->where('hidden', false)
            ->sum('amount');
    }
}
