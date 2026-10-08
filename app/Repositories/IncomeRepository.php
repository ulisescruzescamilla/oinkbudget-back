<?php

namespace App\Repositories;

use App\DataTransferObjects\BalanceData;
use App\DataTransferObjects\IncomeData;
use App\Enums\BalanceTypeEnum;
use App\Models\Income;
use Illuminate\Support\Facades\DB;

class IncomeRepository
{
    public function __construct(
        private readonly BalanceRepository $balanceRepository,
        private readonly AccountRepository $accountRepository,
    ) {}

    public function store(IncomeData $data): Income
    {
        if ($data->client_id) {
            $existing = Income::query()->where('client_id', $data->client_id)->first();

            if ($existing) {
                return $existing->fresh('balance');
            }
        }

        return DB::transaction(function () use ($data) {
            $income = Income::query()->create($data->toArray());
            $this->accountRepository->deposit($income->account_id, $data->amount);

            $account = $income->account;

            $balanceData = new BalanceData(
                description: $data->description,
                amount: $data->amount,
                type: BalanceTypeEnum::INCOME,
                account_name: $account->name,
                account_id: $data->account_id,
                balanceable_type: $income::class,
                balanceable_id: $income->id,
                created_at: $data->created_at,
            );

            $this->balanceRepository->store($balanceData);

            return $income->fresh('balance');
        });
    }

    public function update(Income $income, IncomeData $data): Income
    {
        return DB::transaction(function () use ($income, $data) {
            // revert the old amount from the account it was deposited in
            $this->accountRepository->withdraw($income->account_id, (float) $income->amount);

            $income->update($data->toArray());
            $income = $income->fresh();
            $this->accountRepository->deposit($income->account_id, $data->amount);

            $account = $income->account;

            $balanceData = new BalanceData(
                description: $data->description,
                amount: $data->amount,
                type: BalanceTypeEnum::INCOME,
                account_name: $account->name,
                account_id: $data->account_id,
                balanceable_type: $income::class,
                balanceable_id: $income->id,
            );

            if ($income->balance) {
                $this->balanceRepository->update($income->balance, $balanceData);
            } else {
                $balance = $this->balanceRepository->store($balanceData);
                $income->balance()->save($balance);
            }

            return $income->fresh('balance');
        });
    }

    public function delete(Income $income): void
    {
        DB::transaction(function () use ($income) {
            $this->accountRepository->withdraw($income->account_id, (float) $income->amount);

            $income->balance()?->delete();
            $income->delete();
        });
    }
}
