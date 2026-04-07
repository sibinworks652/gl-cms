<?php

use Modules\Gallery\Controllers\GalleryController;
use Illuminate\Support\Facades\Route;


Route::prefix('modules')->name('api.modules.')->group(function () {
    Route::get('gallery', [GalleryController::class, 'apiGalleries'])->name('gallery.index');
    Route::get('gallery/{slug}', [GalleryController::class, 'apiGallery'])->name('gallery.show');
});