<?php

use Illuminate\Support\Facades\Route;
use Modules\FormBuilder\Controllers\FormController;

Route::get('forms/{form:slug}', [FormController::class, 'showPublic'])
    ->name('forms.public.show');
Route::post('forms/{form:slug}', [FormController::class, 'storePublic'])
    ->name('forms.public.submit');

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('forms', [FormController::class, 'index'])
        ->middleware('permission:forms.view,admin')
        ->name('forms.index');

    Route::get('forms/create', [FormController::class, 'create'])
        ->middleware('permission:forms.create,admin')
        ->name('forms.create');

    Route::post('forms', [FormController::class, 'store'])
        ->middleware('permission:forms.create,admin')
        ->name('forms.store');

    Route::get('forms/{form}/view', [FormController::class, 'show'])
        ->middleware('permission:forms.view,admin')
        ->name('forms.view');

    Route::get('forms/{form}/submissions', [FormController::class, 'submissions'])
        ->middleware('permission:forms.view,admin')
        ->name('forms.submissions');

    Route::get('forms/{form}/edit', [FormController::class, 'edit'])
        ->middleware('permission:forms.update,admin')
        ->name('forms.edit');

    Route::put('forms/{form}', [FormController::class, 'update'])
        ->middleware('permission:forms.update,admin')
        ->name('forms.update');

    Route::delete('forms/{form}', [FormController::class, 'destroy'])
        ->middleware('permission:forms.delete,admin')
        ->name('forms.destroy');
});
