<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Accounts
Route::post('accounts', [AccountController::class, 'store']);
Route::get('accounts', [AccountController::class, 'index']);
Route::put('accounts/{account}', [AccountController::class, 'update']);
Route::delete('accounts/{account}', [AccountController::class, 'destroy']);
Route::post('accounts/transfer', [AccountController::class, 'transfer']);
Route::post('accounts/{id}/restore', [AccountController::class, 'restore']);

// Budgets
Route::get('budgets', [BudgetController::class, 'index']);
Route::post('budgets', [BudgetController::class, 'store']);
Route::put('budgets/{budget}', [BudgetController::class, 'update']);
Route::delete('budgets/{budget}', [BudgetController::class, 'destroy']);

// Expenses
Route::get('expenses', [ExpenseController::class, 'index']);
Route::post('expenses', [ExpenseController::class, 'store']);
Route::put('expenses/{expense}', [ExpenseController::class, 'update']);
Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy']);

// Incomes
Route::get('incomes', [IncomeController::class, 'index']);
Route::post('incomes', [IncomeController::class, 'store']);
Route::put('incomes/{income}', [IncomeController::class, 'update']);
Route::delete('incomes/{income}', [IncomeController::class, 'destroy']);
