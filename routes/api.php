<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/users/sendMoney', [UserController::class, 'sendMoney']);
    Route::get('/users/me', [UserController::class, 'getAuthenticatedUser']);
    Route::post('/users/depositMoney', [UserController::class, 'depositMoney']);
    Route::get('/users/transactions', [UserController::class, 'getTransactionLogs']);
    

});

Route::get('/users', [UserController::class, 'index']);

Route::put('/users/edit/{id}', [UserController::class, 'editUser']);
Route::post('/users', [UserController::class, 'createUser']);

Route::post('/users/login', [UserController::class, 'login']);

Route::delete('/users/delete/{id}', [UserController::class, 'deleteUser']);
Route::post('/users/logout', [UserController::class, 'logout']);


Route::delete('/transactions/{transactionId}', [UserController::class, 'deleteTransaction']);
