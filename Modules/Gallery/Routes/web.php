<?php

use Illuminate\Support\Facades\Route;
use Modules\Gallery\Controllers\GalleryController;

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('gallery', [GalleryController::class, 'index'])
        ->middleware('permission:gallery.view,admin')
        ->name('gallery.index');

    Route::get('gallery/create', [GalleryController::class, 'create'])
        ->middleware('permission:gallery.create,admin')
        ->name('gallery.create');

    Route::post('gallery', [GalleryController::class, 'store'])
        ->middleware('permission:gallery.create,admin')
        ->name('gallery.store');

    Route::get('gallery/{gallery}/edit', [GalleryController::class, 'edit'])
        ->middleware('permission:gallery.update,admin')
        ->name('gallery.edit');

    Route::put('gallery/{gallery}', [GalleryController::class, 'update'])
        ->middleware('permission:gallery.update,admin')
        ->name('gallery.update');

    Route::delete('gallery/{gallery}', [GalleryController::class, 'destroy'])
        ->middleware('permission:gallery.delete,admin')
        ->name('gallery.destroy');
});
