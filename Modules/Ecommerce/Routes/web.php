<?php

use Illuminate\Support\Facades\Route;
use Modules\Ecommerce\Controllers\Admin\CategoryController as AdminCategoryController;
use Modules\Ecommerce\Controllers\Admin\OrderController as AdminOrderController;
use Modules\Ecommerce\Controllers\Admin\ProductController as AdminProductController;
use Modules\Ecommerce\Controllers\Admin\VendorController as AdminVendorController;
use Modules\Ecommerce\Controllers\Vendor\VendorController as VendorDashboardController;
use Modules\Ecommerce\Controllers\Vendor\ProductController as VendorProductController;
use Modules\Ecommerce\Controllers\Web\CartController;
use Modules\Ecommerce\Controllers\Web\CatalogController;
use Modules\Ecommerce\Controllers\Web\CheckoutController;
use Modules\Ecommerce\Controllers\Web\OrderController;

Route::prefix('shop')->name('ecommerce.')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('shop.index');
    Route::get('/products/{slug}', [CatalogController::class, 'show'])->name('products.show');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::middleware('auth')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });
});

Route::prefix('admin/ecommerce')->middleware('auth:admin')->name('admin.ecommerce.')->group(function () {
    Route::get('/categories', [AdminCategoryController::class, 'index'])->middleware('permission:product-categories.view,admin')->name('categories.index');
    Route::get('/categories/create', [AdminCategoryController::class, 'create'])->middleware('permission:product-categories.create,admin')->name('categories.create');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->middleware('permission:product-categories.create,admin')->name('categories.store');
    Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->middleware('permission:product-categories.update,admin')->name('categories.edit');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->middleware('permission:product-categories.update,admin')->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->middleware('permission:product-categories.delete,admin')->name('categories.destroy');

    Route::get('/vendors', [AdminVendorController::class, 'index'])->middleware('permission:vendors.view,admin')->name('vendors.index');
    Route::get('/vendors/create', [AdminVendorController::class, 'create'])->middleware('permission:vendors.create,admin')->name('vendors.create');
    Route::post('/vendors', [AdminVendorController::class, 'store'])->middleware('permission:vendors.create,admin')->name('vendors.store');
    Route::get('/vendors/{vendor}/edit', [AdminVendorController::class, 'edit'])->middleware('permission:vendors.update,admin')->name('vendors.edit');
    Route::put('/vendors/{vendor}', [AdminVendorController::class, 'update'])->middleware('permission:vendors.update,admin')->name('vendors.update');
    Route::delete('/vendors/{vendor}', [AdminVendorController::class, 'destroy'])->middleware('permission:vendors.delete,admin')->name('vendors.destroy');

    Route::get('/products', [AdminProductController::class, 'index'])->middleware('permission:products.view,admin')->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->middleware('permission:products.create,admin')->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->middleware('permission:products.create,admin')->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->middleware('permission:products.update,admin')->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->middleware('permission:products.update,admin')->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->middleware('permission:products.delete,admin')->name('products.destroy');

    Route::get('/orders', [AdminOrderController::class, 'index'])->middleware('permission:orders.view,admin')->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->middleware('permission:orders.view,admin')->name('orders.show');
    Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->middleware('permission:orders.update,admin')->name('orders.update');

    Route::post('/vendors/{vendor}/approve', [AdminVendorController::class, 'approve'])->middleware('permission:vendors.update,admin')->name('vendors.approve');
    Route::post('/vendors/{vendor}/reject', [AdminVendorController::class, 'reject'])->middleware('permission:vendors.update,admin')->name('vendors.reject');
});

Route::prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/register', [VendorDashboardController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [VendorDashboardController::class, 'register'])->name('register.store');
});

Route::prefix('vendor')->name('vendor.')->middleware('auth:web')->group(function () {
    Route::get('/dashboard', [VendorDashboardController::class, 'dashboard'])->name('dashboard');

    Route::get('/products', [VendorProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [VendorProductController::class, 'create'])->name('products.create');
    Route::post('/products', [VendorProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [VendorProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [VendorProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [VendorProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/orders', [VendorDashboardController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [VendorDashboardController::class, 'showOrder'])->name('orders.show');
    Route::put('/orders/{order}', [VendorDashboardController::class, 'updateOrderStatus'])->name('orders.update');
});
