<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Controllers\MenuController;

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('menus', [MenuController::class, 'index'])
        ->middleware('permission:menus.view,admin')
        ->name('menus.index');

    Route::get('menus/create', [MenuController::class, 'create'])
        ->middleware('permission:menus.create,admin')
        ->name('menus.create');

    Route::post('menus', [MenuController::class, 'store'])
        ->middleware('permission:menus.create,admin')
        ->name('menus.store');

    Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])
        ->middleware('permission:menus.update,admin')
        ->name('menus.edit');

    Route::put('menus/{menu}', [MenuController::class, 'update'])
        ->middleware('permission:menus.update,admin')
        ->name('menus.update');

    Route::delete('menus/{menu}', [MenuController::class, 'destroy'])
        ->middleware('permission:menus.delete,admin')
        ->name('menus.destroy');
    Route::get('menus/{menu}/view', [MenuController::class, 'view'])
        ->middleware('permission:menus.view,admin')
        ->name('menus.view');
});
