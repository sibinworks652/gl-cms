<?php

use Illuminate\Support\Facades\Route;
use Modules\Ecommerce\Controllers\Api\AuthController;
use Modules\Ecommerce\Controllers\Api\CartController;
use Modules\Ecommerce\Controllers\Api\CatalogController;
use Modules\Ecommerce\Controllers\Api\OrderController;
use Modules\Ecommerce\Controllers\Api\PaymentController;

Route::prefix('modules/ecommerce')->name('api.ecommerce.')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    Route::get('/categories', [CatalogController::class, 'categories'])->name('categories.index');
    Route::get('/products', [CatalogController::class, 'products'])->name('products.index');
    Route::get('/products/{slug}', [CatalogController::class, 'product'])->name('products.show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
        Route::post('/cart/items', [CartController::class, 'store'])->name('cart.store');
        Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

        Route::get('/orders/{order}/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
    });
});
