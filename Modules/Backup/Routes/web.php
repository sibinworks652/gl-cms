<?php

use Illuminate\Support\Facades\Route;
use Modules\Backup\Controllers\BackupController;
use Modules\Backup\Controllers\GoogleDriveController;

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('backups', [BackupController::class, 'index'])
        ->middleware('permission:backups.view,admin')
        ->name('backups.index');

    Route::post('backups', [BackupController::class, 'store'])
        ->middleware('permission:backups.create,admin')
        ->name('backups.store');

    Route::get('backups/google/redirect', [GoogleDriveController::class, 'redirect'])
        ->middleware('permission:backups.create,admin')
        ->name('backups.google.redirect');

    Route::get('backups/google/callback', [GoogleDriveController::class, 'callback'])
        ->middleware('permission:backups.create,admin')
        ->name('backups.google.callback');

    Route::delete('backups/google', [GoogleDriveController::class, 'disconnect'])
        ->middleware('permission:backups.create,admin')
        ->name('backups.google.disconnect');

    Route::get('backups/{filename}/download', [BackupController::class, 'download'])
        ->middleware('permission:backups.view,admin')
        ->where('filename', '.*\.zip')
        ->name('backups.download');

    Route::delete('backups/{filename}', [BackupController::class, 'destroy'])
        ->middleware('permission:backups.delete,admin')
        ->where('filename', '.*\.zip')
        ->name('backups.destroy');
});
