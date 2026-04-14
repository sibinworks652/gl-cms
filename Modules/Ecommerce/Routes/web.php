<?php

use Illuminate\Support\Facades\Route;
use Modules\Ecommerce\Controllers\Admin\AttributeController;
use Modules\Ecommerce\Controllers\Admin\BrandController;
use Modules\Ecommerce\Controllers\Admin\CategoryController as AdminCategoryController;
use Modules\Ecommerce\Controllers\Admin\CouponController;
use Modules\Ecommerce\Controllers\Admin\DiscountController;
use Modules\Ecommerce\Controllers\Admin\InventoryController;
use Modules\Ecommerce\Controllers\Admin\OrderController as AdminOrderController;
use Modules\Ecommerce\Controllers\Admin\ProductController as AdminProductController;
use Modules\Ecommerce\Controllers\Admin\TagController;
use Modules\Ecommerce\Controllers\Admin\VendorController as AdminVendorController;
use Modules\Ecommerce\Controllers\Vendor\VendorController as VendorDashboardController;
use Modules\Ecommerce\Controllers\Vendor\VendorInventoryController;
use Modules\Ecommerce\Controllers\Vendor\VendorSettingsController;
use Modules\Ecommerce\Controllers\Vendor\VendorEarningsController;
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
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

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

    Route::get('/brands', [BrandController::class, 'index'])->middleware('permission:products.view,admin')->name('brands.index');
    Route::get('/brands/create', [BrandController::class, 'create'])->middleware('permission:products.create,admin')->name('brands.create');
    Route::post('/brands', [BrandController::class, 'store'])->middleware('permission:products.create,admin')->name('brands.store');
    Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->middleware('permission:products.update,admin')->name('brands.edit');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->middleware('permission:products.update,admin')->name('brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->middleware('permission:products.delete,admin')->name('brands.destroy');

    Route::get('/attributes', [AttributeController::class, 'index'])->middleware('permission:products.view,admin')->name('attributes.index');
    Route::get('/attributes/create', [AttributeController::class, 'create'])->middleware('permission:products.create,admin')->name('attributes.create');
    Route::post('/attributes', [AttributeController::class, 'store'])->middleware('permission:products.create,admin')->name('attributes.store');
    Route::get('/attributes/{attribute}/edit', [AttributeController::class, 'edit'])->middleware('permission:products.update,admin')->name('attributes.edit');
    Route::put('/attributes/{attribute}', [AttributeController::class, 'update'])->middleware('permission:products.update,admin')->name('attributes.update');
    Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy'])->middleware('permission:products.delete,admin')->name('attributes.destroy');
    Route::get('/attributes/{attribute}/options', [AttributeController::class, 'getOptions'])->name('attributes.options');

    Route::get('/tags', [TagController::class, 'index'])->middleware('permission:products.view,admin')->name('tags.index');
    Route::get('/tags/create', [TagController::class, 'create'])->middleware('permission:products.create,admin')->name('tags.create');
    Route::post('/tags', [TagController::class, 'store'])->middleware('permission:products.create,admin')->name('tags.store');
    Route::get('/tags/{tag}/edit', [TagController::class, 'edit'])->middleware('permission:products.update,admin')->name('tags.edit');
    Route::put('/tags/{tag}', [TagController::class, 'update'])->middleware('permission:products.update,admin')->name('tags.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->middleware('permission:products.delete,admin')->name('tags.destroy');
    Route::get('/tags/search', [TagController::class, 'search'])->name('tags.search');

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

    Route::get('/discounts', [DiscountController::class, 'index'])->middleware('permission:products.view,admin')->name('discounts.index');
    Route::get('/discounts/create', [DiscountController::class, 'create'])->middleware('permission:products.create,admin')->name('discounts.create');
    Route::post('/discounts', [DiscountController::class, 'store'])->middleware('permission:products.create,admin')->name('discounts.store');
    Route::get('/discounts/{discount}/edit', [DiscountController::class, 'edit'])->middleware('permission:products.update,admin')->name('discounts.edit');
    Route::put('/discounts/{discount}', [DiscountController::class, 'update'])->middleware('permission:products.update,admin')->name('discounts.update');
    Route::delete('/discounts/{discount}', [DiscountController::class, 'destroy'])->middleware('permission:products.delete,admin')->name('discounts.destroy');

    Route::get('/coupons', [CouponController::class, 'index'])->middleware('permission:products.view,admin')->name('coupons.index');
    Route::get('/coupons/create', [CouponController::class, 'create'])->middleware('permission:products.create,admin')->name('coupons.create');
    Route::post('/coupons', [CouponController::class, 'store'])->middleware('permission:products.create,admin')->name('coupons.store');
    Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->middleware('permission:products.update,admin')->name('coupons.edit');
    Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->middleware('permission:products.update,admin')->name('coupons.update');
    Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->middleware('permission:products.delete,admin')->name('coupons.destroy');

    Route::get('/orders', [AdminOrderController::class, 'index'])->middleware('permission:orders.view,admin')->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->middleware('permission:orders.view,admin')->name('orders.show');
    Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->middleware('permission:orders.update,admin')->name('orders.update');

    Route::get('/inventory', [InventoryController::class, 'index'])->middleware('permission:products.view,admin')->name('inventory.index');
    Route::get('/inventory/{inventory}', [InventoryController::class, 'show'])->middleware('permission:products.view,admin')->name('inventory.show');
    Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->middleware('permission:products.update,admin')->name('inventory.update');
    Route::post('/inventory/{inventory}/restock', [InventoryController::class, 'restock'])->middleware('permission:products.update,admin')->name('inventory.restock');

    Route::post('/vendors/{vendor}/approve', [AdminVendorController::class, 'approve'])->middleware('permission:vendors.update,admin')->name('vendors.approve');
    Route::post('/vendors/{vendor}/reject', [AdminVendorController::class, 'reject'])->middleware('permission:vendors.update,admin')->name('vendors.reject');
});

Route::prefix('vendor')->name('vendor.')->middleware('vendor.module')->group(function () {
    Route::get('/login', [VendorDashboardController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [VendorDashboardController::class, 'login'])->name('login.store');
    Route::post('/logout', [VendorDashboardController::class, 'logout'])->name('logout');

    Route::get('/register', [VendorDashboardController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [VendorDashboardController::class, 'register'])->name('register.store');

    Route::get('/pending', [VendorDashboardController::class, 'pending'])->name('pending');
});

Route::prefix('vendor')->name('vendor.')->middleware(['vendor.module', 'auth:web', 'vendor.approved'])->group(function () {
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

    Route::get('/inventory', [VendorInventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{inventory}', [VendorInventoryController::class, 'show'])->name('inventory.show');
    Route::post('/inventory/{inventory}/restock', [VendorInventoryController::class, 'restock'])->name('inventory.restock');
    Route::put('/inventory/{inventory}', [VendorInventoryController::class, 'update'])->name('inventory.update');

    Route::get('/earnings', [VendorEarningsController::class, 'index'])->name('earnings.index');
    Route::get('/earnings/transactions', [VendorEarningsController::class, 'transactions'])->name('earnings.transactions');
    Route::get('/earnings/payouts', [VendorEarningsController::class, 'payouts'])->name('earnings.payouts');

    Route::get('/settings/profile', [VendorSettingsController::class, 'profile'])->name('settings.profile');
    Route::put('/settings/profile', [VendorSettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::get('/settings/store', [VendorSettingsController::class, 'storeSettings'])->name('settings.store');
    Route::put('/settings/store', [VendorSettingsController::class, 'updateStoreSettings'])->name('settings.store.update');
    Route::get('/settings/bank', [VendorSettingsController::class, 'bankDetails'])->name('settings.bank');
    Route::put('/settings/bank', [VendorSettingsController::class, 'updateBankDetails'])->name('settings.bank.update');
    Route::get('/settings/notifications', [VendorSettingsController::class, 'notifications'])->name('settings.notifications');
});
