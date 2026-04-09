<?php

use Illuminate\Support\Facades\Route;
use Modules\Careers\Controllers\Admin\JobApplicationController;
use Modules\Careers\Controllers\Admin\JobCategoryController;
use Modules\Careers\Controllers\Admin\JobController;
use Modules\Careers\Controllers\Web\CareerController;

Route::get('careers', [CareerController::class, 'index'])
    ->name('careers.index');

Route::get('careers/apply/{slug}', [CareerController::class, 'apply'])
    ->name('careers.apply.show');

Route::post('careers/apply/{slug}', [CareerController::class, 'submit'])
    ->name('careers.apply.submit');

Route::get('careers/{slug}', [CareerController::class, 'show'])
    ->name('careers.show');

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('jobs', [JobController::class, 'index'])
        ->middleware('permission:careers.jobs.view,admin')
        ->name('jobs.index');

    Route::get('jobs/create', [JobController::class, 'create'])
        ->middleware('permission:careers.jobs.create,admin')
        ->name('jobs.create');

    Route::post('jobs', [JobController::class, 'store'])
        ->middleware('permission:careers.jobs.create,admin')
        ->name('jobs.store');

    Route::get('jobs/{job}/edit', [JobController::class, 'edit'])
        ->middleware('permission:careers.jobs.update,admin')
        ->name('jobs.edit');

    Route::put('jobs/{job}', [JobController::class, 'update'])
        ->middleware('permission:careers.jobs.update,admin')
        ->name('jobs.update');

    Route::delete('jobs/{job}', [JobController::class, 'destroy'])
        ->middleware('permission:careers.jobs.delete,admin')
        ->name('jobs.destroy');

    Route::get('job-categories', [JobCategoryController::class, 'index'])
        ->middleware('permission:careers.categories.view,admin')
        ->name('job-categories.index');

    Route::get('job-categories/create', [JobCategoryController::class, 'create'])
        ->middleware('permission:careers.categories.create,admin')
        ->name('job-categories.create');

    Route::post('job-categories', [JobCategoryController::class, 'store'])
        ->middleware('permission:careers.categories.create,admin')
        ->name('job-categories.store');

    Route::get('job-categories/{job_category}/edit', [JobCategoryController::class, 'edit'])
        ->middleware('permission:careers.categories.update,admin')
        ->name('job-categories.edit');

    Route::put('job-categories/{job_category}', [JobCategoryController::class, 'update'])
        ->middleware('permission:careers.categories.update,admin')
        ->name('job-categories.update');

    Route::delete('job-categories/{job_category}', [JobCategoryController::class, 'destroy'])
        ->middleware('permission:careers.categories.delete,admin')
        ->name('job-categories.destroy');

    Route::get('applications', [JobApplicationController::class, 'index'])
        ->middleware('permission:careers.applications.view,admin')
        ->name('applications.index');

    Route::get('applications/{application}', [JobApplicationController::class, 'show'])
        ->middleware('permission:careers.applications.view,admin')
        ->name('applications.show');

    Route::patch('applications/{application}/status', [JobApplicationController::class, 'updateStatus'])
        ->middleware('permission:careers.applications.update,admin')
        ->name('applications.status.update');

    Route::get('applications/{application}/resume', [JobApplicationController::class, 'downloadResume'])
        ->middleware('permission:careers.applications.view,admin')
        ->name('applications.resume.download');
});
