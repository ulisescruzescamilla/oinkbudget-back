<?php

namespace App\Repositories;

use App\DataTransferObjects\BalanceData;
use App\DataTransferObjects\IncomeData;
use App\Enums\BalanceTypeEnum;
use App\Models\Income;

class IncomeRepository
{
    public function __construct(
        private readonly BalanceRepository $balanceRepository,
    ) {}

    public function store(IncomeData $data): Income
    {
        if ($data->client_id) {
            $existing = Income::query()->where('client_id', $data->client_id)->first();

            if ($existing) {
                return $existing->fresh('balance');
            }
        }

        $income = Income::query()->create($data->toArray());

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

        $balance = $this->balanceRepository->store($balanceData);

        return $income->fresh('balance');
    }

    public function update(Income $income, IncomeData $data): Income
    {
        $income->update($data->toArray());
        $income = $income->fresh();

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
    }

    public function delete(Income $income): void
    {
        $income->balance()?->delete();
        $income->delete();
    }
}
