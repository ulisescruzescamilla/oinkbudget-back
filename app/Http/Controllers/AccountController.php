<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\TransferAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Repositories\AccountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AccountController extends Controller
{
    public function __construct(private readonly AccountRepository $accountRepository) {}

    public function index()
    {
        $accounts = $this->accountRepository->get();

        return response()->json($accounts, 200);
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = $this->accountRepository->store($request->validated());

        return response()->json($account, 201);
    }

    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        $account = $this->accountRepository->update($account, $request->validated());

        return response()->json($account);
    }

    public function transfer(TransferAccountRequest $request)
    {
        $this->accountRepository->transfer($request->input('account_from'), $request->input('account_to'), $request->input('amount'));

        return response()->json([], 200);
    }

    public function destroy(Account $account): Response
    {
        $this->accountRepository->delete($account);

        return response()->noContent();
    }

    public function restore(int $id): JsonResponse
    {
        $account = Account::query()->withTrashed()->findOrFail($id);
        $account = $this->accountRepository->restore($account);

        return response()->json($account);
    }
}
