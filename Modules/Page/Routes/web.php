<?php

use Illuminate\Support\Facades\Route;
use Modules\Page\Controllers\PageController;

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('pages', [PageController::class, 'index'])
        ->middleware('permission:pages.view,admin')
        ->name('pages.index');

    Route::get('pages/create', [PageController::class, 'create'])
        ->middleware('permission:pages.create,admin')
        ->name('pages.create');

    Route::post('pages', [PageController::class, 'store'])
        ->middleware('permission:pages.create,admin')
        ->name('pages.store');

    Route::get('pages/{page}/edit', [PageController::class, 'edit'])
        ->middleware('permission:pages.update,admin')
        ->name('pages.edit');

    Route::put('pages/{page}', [PageController::class, 'update'])
        ->middleware('permission:pages.update,admin')
        ->name('pages.update');

    Route::delete('pages/{page}', [PageController::class, 'destroy'])
        ->middleware('permission:pages.delete,admin')
        ->name('pages.destroy');
});

Route::get('{page:slug}', [PageController::class, 'show'])
    ->where('page', '^(?!admin$|admin/|login$|logout$|menu-preview$|forms/).+')
    ->name('pages.show');
