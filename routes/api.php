<?php

use App\Http\Controllers\AccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Accounts
Route::post('accounts', [AccountController::class, 'store']);
Route::put('accounts/{account}', [AccountController::class, 'update']);
Route::delete('accounts/{account}', [AccountController::class, 'destroy']);
Route::post('accounts/{id}/restore', [AccountController::class, 'restore']);

