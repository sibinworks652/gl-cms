<?php

use Illuminate\Support\Facades\Route;
use Modules\Services\Controllers\AdminServiceController;
use Modules\Services\Controllers\FrontServiceController;
use Modules\Services\Controllers\ServiceCategoryController;

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('services', [AdminServiceController::class, 'index'])
        ->middleware('permission:services.view,admin')
        ->name('services.index');

    Route::get('services/create', [AdminServiceController::class, 'create'])
        ->middleware('permission:services.create,admin')
        ->name('services.create');

    Route::post('services', [AdminServiceController::class, 'store'])
        ->middleware('permission:services.create,admin')
        ->name('services.store');

    Route::get('services/{service}/edit', [AdminServiceController::class, 'edit'])
        ->middleware('permission:services.update,admin')
        ->name('services.edit');

    Route::put('services/{service}', [AdminServiceController::class, 'update'])
        ->middleware('permission:services.update,admin')
        ->name('services.update');

    Route::post('services/reorder', [AdminServiceController::class, 'reorder'])
        ->middleware('permission:services.update,admin')
        ->name('services.reorder');

    Route::delete('services/{service}', [AdminServiceController::class, 'destroy'])
        ->middleware('permission:services.delete,admin')
        ->name('services.destroy');

    Route::get('service-categories', [ServiceCategoryController::class, 'index'])
        ->middleware('permission:service-categories.view,admin')
        ->name('service-categories.index');

    Route::get('service-categories/create', [ServiceCategoryController::class, 'create'])
        ->middleware('permission:service-categories.create,admin')
        ->name('service-categories.create');

    Route::post('service-categories', [ServiceCategoryController::class, 'store'])
        ->middleware('permission:service-categories.create,admin')
        ->name('service-categories.store');

    Route::get('service-categories/{category}/edit', [ServiceCategoryController::class, 'edit'])
        ->middleware('permission:service-categories.update,admin')
        ->name('service-categories.edit');

    Route::put('service-categories/{category}', [ServiceCategoryController::class, 'update'])
        ->middleware('permission:service-categories.update,admin')
        ->name('service-categories.update');

    Route::post('service-categories/reorder', [ServiceCategoryController::class, 'reorder'])
        ->middleware('permission:service-categories.update,admin')
        ->name('service-categories.reorder');

    Route::delete('service-categories/{category}', [ServiceCategoryController::class, 'destroy'])
        ->middleware('permission:service-categories.delete,admin')
        ->name('service-categories.destroy');
});

Route::get('services', [FrontServiceController::class, 'index'])->name('services.index');
Route::get('services/{slug}', [FrontServiceController::class, 'show'])->name('services.show');
