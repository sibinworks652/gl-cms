<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Controllers\SettingsController;

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('settings', [SettingsController::class, 'show'])
        ->middleware('permission:settings.view,admin')
        ->name('settings.show');

    Route::get('settings/edit', [SettingsController::class, 'edit'])
        ->middleware('permission:settings.update,admin')
        ->name('settings.edit');

    Route::get('settings/{section}/edit', [SettingsController::class, 'editSection'])
        ->middleware('permission:settings.update,admin')
        ->name('settings.section.edit');

    Route::put('settings', [SettingsController::class, 'update'])
        ->middleware('permission:settings.update,admin')
        ->name('settings.update');

    Route::put('settings/{section}', [SettingsController::class, 'updateSection'])
        ->middleware('permission:settings.update,admin')
        ->name('settings.section.update');
    Route::post('settings/dark-mode', [SettingsController::class, 'darkMode'])
        ->name('dark-mode');

});
