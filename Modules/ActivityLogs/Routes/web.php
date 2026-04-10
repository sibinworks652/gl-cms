<?php

use Illuminate\Support\Facades\Route;
use Modules\ActivityLogs\Controllers\ActivityLogController;
use Modules\ActivityLogs\Controllers\LoginHistoryController;

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity-logs.index');

    Route::get('activity-logs/feed', [ActivityLogController::class, 'feed'])
        ->name('activity-logs.feed');

    Route::get('login-histories', [LoginHistoryController::class, 'index'])
        ->name('login-histories.index');
});
