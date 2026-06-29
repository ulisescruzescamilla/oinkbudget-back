<?php

namespace App\Repositories;

use App\DataTransferObjects\AccountData;
use App\Models\Account;

class AccountRepository
{
    public function get()
    {
        return Account::query()->get();
    }

    public function transfer(Account $accountFrom, Account $accountTo, float $amount): void
    {
        $accountFrom->amount = $accountFrom->amount - $amount;
        $accountTo->amount = $accountTo->amount + $amount;

        $accountFrom->save();
        $accountTo->save();
    }

    public function store(AccountData $data): Account
    {
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

    public function amountAvailable() : float
    {
        return Account::query()
            ->where('hidden', false)
            ->sum('amount');
    }
}
