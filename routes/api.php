<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ShiftApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\CashierApiController;
use App\Http\Controllers\Api\SaleApiController;
use App\Http\Controllers\Api\HealthApiController;
use App\Http\Controllers\Api\AuthApiController;

// Health check (без версии, для пингования)
Route::get('health', [HealthApiController::class, 'check']);

// Публичные маршруты авторизации (не требуют токена)
Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthApiController::class, 'login']);
    Route::apiResource('products-api', ProductApiController::class)->only([
        'index'
    ]);
});

// Защищённые маршруты (требуют токен)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Управление токенами
    Route::post('auth/logout', [AuthApiController::class, 'logout']);
    Route::post('auth/logout-all', [AuthApiController::class, 'logoutAll']);
    Route::get('auth/user', [AuthApiController::class, 'user']);

    // API для товаров
    Route::apiResource('products-api', ProductApiController::class)->only([
        'show', 'store', 'update', 'destroy'
    ]);

    // Управление скидками
    Route::post('products-api/{id}/discount', [ProductApiController::class, 'setDiscount']);
    Route::post('products-api/bulk-discount', [ProductApiController::class, 'bulkSetDiscount']);
    Route::delete('products-api/{id}/discount', [ProductApiController::class, 'removeDiscount']);
    Route::post('products-api/bulk-remove-discount', [ProductApiController::class, 'bulkRemoveDiscount']);

    // API для категорий
    Route::apiResource('categories-api', CategoryApiController::class)->only([
        'index', 'show', 'store', 'update', 'destroy'
    ]);

    // API для кассиров
    Route::apiResource('cashiers-api', CashierApiController::class)->only([
        'index', 'show', 'store', 'update', 'destroy'
    ]);

    // API для продаж
    Route::apiResource('sales-api', SaleApiController::class)->only([
        'index', 'show', 'store', 'update', 'destroy'
    ]);

    // Получение чека по продаже
    Route::get('sales-api/{id}/receipt', [SaleApiController::class, 'receipt']);

    // API для смен
    Route::get('shifts', [ShiftApiController::class, 'index']);
    Route::get('shifts/current', [ShiftApiController::class, 'current']);
    Route::post('shifts/open', [ShiftApiController::class, 'open']);
    Route::post('shifts/{id}/close', [ShiftApiController::class, 'close']);
    Route::delete('shifts/{id}', [ShiftApiController::class, 'destroy']);
});
