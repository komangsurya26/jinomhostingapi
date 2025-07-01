<?php

use App\Http\Controllers\Api\PaymentCallbackController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderVpsController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\Admin\PromoCodeController as AdminPromoCodeController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\ServiceController;

Route::group([
    'prefix' => 'v1'
], function ($router) {
    // auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // order
    Route::group([
        'middleware' => ['auth:api', 'verified'],
        'prefix' => 'orders',
    ], function ($router) {
        // vps
        Route::prefix('vps')->group(function () {
            Route::post('/', [OrderVpsController::class, 'store']);
            Route::get('/', [OrderVpsController::class, 'get']);
        });
        // shared hosting
        Route::prefix('shared-hosting')->group(function () {
            //
        });
        // colocation
        Route::prefix('colocation')->group(function () {
            //
        });

        Route::get('/', [OrderVpsController::class, 'get']);
        Route::post('/{orderId}', [OrderVpsController::class, 'update']);
    });

    // payments
    Route::prefix('payments')->group(function () {
        Route::post('/callback', [PaymentCallbackController::class, 'callback']);
        Route::post('/simulation', [PaymentCallbackController::class, 'simulation']);
    });

    // service
    Route::prefix('service')->group(function () {
        Route::get('/{productType}', [ServiceController::class, 'get']);
    });

    // promo codes
    Route::group([
        'middleware' => ['auth:api', 'verified'],
        'prefix' => 'promo-codes',
    ], function ($router) {
        Route::get('/', [PromoCodeController::class, 'apply']);
    });

    // Admin
    Route::group([
        'middleware' => ['auth:api', 'verified'],
        'prefix' => 'admin',
    ], function ($router) {
        // promo codes
        Route::prefix('promo-codes')->group(function () {
            Route::post('/', [AdminPromoCodeController::class, 'store']);
            Route::get('/', [AdminPromoCodeController::class, 'get']);
            Route::post('/{id}', [AdminPromoCodeController::class, 'update']);
            Route::delete('/{id}', [AdminPromoCodeController::class, 'delete']);
        });

        //product
        Route::prefix('product')->group(function () {
            Route::post('/{productType}', [AdminProductController::class, 'store']);
            Route::get('/{productType}', [AdminProductController::class, 'get']);
            Route::post('/{productType}/{productId}', [AdminProductController::class, 'update']);
        });
    });
});
