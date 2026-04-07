<?php

use Illuminate\Support\Facades\Route;
use Modules\Banner\Controllers\BannerController;

Route::get('banner-preview', [BannerController::class, 'preview'])
    ->name('banner.preview');

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('banners', [BannerController::class, 'index'])
        ->middleware('permission:banners.view,admin')
        ->name('banners.index');

    Route::get('banners/create', [BannerController::class, 'create'])
        ->middleware('permission:banners.create,admin')
        ->name('banners.create');

    Route::post('banners', [BannerController::class, 'store'])
        ->middleware('permission:banners.create,admin')
        ->name('banners.store');

    Route::get('banners/{banner}', [BannerController::class, 'show'])
        ->middleware('permission:banners.view,admin')
        ->name('banners.show');

    Route::get('banners/{banner}/edit', [BannerController::class, 'edit'])
        ->middleware('permission:banners.update,admin')
        ->name('banners.edit');

    Route::put('banners/{banner}', [BannerController::class, 'update'])
        ->middleware('permission:banners.update,admin')
        ->name('banners.update');

    Route::post('banners/reorder', [BannerController::class, 'reorder'])
        ->middleware('permission:banners.update,admin')
        ->name('banners.reorder');

    Route::delete('banners/{banner}', [BannerController::class, 'destroy'])
        ->middleware('permission:banners.delete,admin')
        ->name('banners.destroy');
});
