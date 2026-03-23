<?php

namespace App\Repositories;

use App\Models\Account;

class AccountRepository
{
    public function store(array $data): Account
    {
        return Account::query()->create($data);
    }

    public function update(Account $account, array $data): Account
    {
        $account->update($data);

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
}
