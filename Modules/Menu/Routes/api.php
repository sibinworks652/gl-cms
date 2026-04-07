<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Controllers\MenuController;

Route::prefix('modules')->name('api.modules.')->group(function () {
    Route::get('menus', [MenuController::class, 'apiMenus'])->name('menus.index');
    Route::get('menus/{location}', [MenuController::class, 'apiMenu'])->name('menus.show');
});
