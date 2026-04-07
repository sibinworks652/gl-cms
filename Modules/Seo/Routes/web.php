<?php

use Illuminate\Support\Facades\Route;
use Modules\Seo\Controllers\SeoController;

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('seo', [SeoController::class, 'index'])
        ->middleware('permission:seo.view,admin')
        ->name('seo.index');

    Route::get('seo/create', [SeoController::class, 'create'])
        ->middleware('permission:seo.create,admin')
        ->name('seo.create');

    Route::post('seo', [SeoController::class, 'store'])
        ->middleware('permission:seo.create,admin')
        ->name('seo.store');

    Route::get('seo/{seo}/edit', [SeoController::class, 'edit'])
        ->middleware('permission:seo.update,admin')
        ->name('seo.edit');

    Route::put('seo/{seo}', [SeoController::class, 'update'])
        ->middleware('permission:seo.update,admin')
        ->name('seo.update');

    Route::delete('seo/{seo}', [SeoController::class, 'destroy'])
        ->middleware('permission:seo.delete,admin')
        ->name('seo.destroy');
});
