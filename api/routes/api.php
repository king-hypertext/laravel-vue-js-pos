<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => app()
    ]);
});
Route::post('login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('stats', [AppController::class, 'stats']);
    Route::apiResource('products', ProductController::class);
    Route::put('products/update-quantity/{product}', [ProductController::class, 'updateQuantity']);
    Route::post('print-reciept/{sale}', [SalesController::class, 'print']);
    Route::apiResource('sales', SalesController::class);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::apiSingleton('user', AuthController::class);
    Route::apiResource('users', UsersController::class);
});
