<?php

use Illuminate\Support\Facades\Route;
use Modules\Email\Controllers\EmailSettingsController;
use Modules\Email\Controllers\EmailTemplateController;
use Modules\Email\Controllers\EmailTestingController;

Route::prefix('admin/email')->middleware('auth:admin')->name('admin.email.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.email.settings.edit'))
        ->middleware('permission:email.view,admin')
        ->name('index');

    Route::get('settings', [EmailSettingsController::class, 'edit'])
        ->middleware('permission:email.view,admin')
        ->name('settings.edit');

    Route::put('settings', [EmailSettingsController::class, 'update'])
        ->middleware('permission:email.settings.update,admin')
        ->name('settings.update');

    Route::get('templates', [EmailTemplateController::class, 'index'])
        ->middleware('permission:email.templates.view,admin')
        ->name('templates.index');

    Route::get('templates/create', [EmailTemplateController::class, 'create'])
        ->middleware('permission:email.templates.create,admin')
        ->name('templates.create');

    Route::post('templates', [EmailTemplateController::class, 'store'])
        ->middleware('permission:email.templates.create,admin')
        ->name('templates.store');

    Route::post('templates/images', [EmailTemplateController::class, 'uploadImage'])
        ->middleware('permission:email.templates.create|email.templates.update,admin')
        ->name('templates.images.store');

    Route::get('templates/{template}/preview', [EmailTemplateController::class, 'preview'])
        ->middleware('permission:email.templates.view,admin')
        ->name('templates.preview');

    Route::get('templates/{template}/edit', [EmailTemplateController::class, 'edit'])
        ->middleware('permission:email.templates.update,admin')
        ->name('templates.edit');

    Route::put('templates/{template}', [EmailTemplateController::class, 'update'])
        ->middleware('permission:email.templates.update,admin')
        ->name('templates.update');

    Route::delete('templates/{template}', [EmailTemplateController::class, 'destroy'])
        ->middleware('permission:email.templates.delete,admin')
        ->name('templates.destroy');

    Route::get('testing', [EmailTestingController::class, 'index'])
        ->middleware('permission:email.testing,admin')
        ->name('testing.index');

    Route::post('testing/send', [EmailTestingController::class, 'send'])
        ->middleware('permission:email.testing,admin')
        ->name('testing.send');

    Route::post('testing/smtp', [EmailTestingController::class, 'smtp'])
        ->middleware('permission:email.testing,admin')
        ->name('testing.smtp');

    Route::get('testing/templates/{template}/preview', [EmailTestingController::class, 'preview'])
        ->middleware('permission:email.testing,admin')
        ->name('testing.preview');
});
