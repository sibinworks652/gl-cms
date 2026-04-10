<?php

use Illuminate\Support\Facades\Route;
use Modules\Testimonials\Controllers\Admin\TestimonialController;
use Modules\Testimonials\Controllers\Web\TestimonialFrontController;

// Route::get('testimonials', [TestimonialFrontController::class, 'index'])
//     ->name('testimonials.index');

// Route::get('testimonials/{slug}', [TestimonialFrontController::class, 'show'])
//     ->name('testimonials.show');

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('testimonials', [TestimonialController::class, 'index'])
        ->middleware('permission:testimonials.view,admin')
        ->name('testimonials.index');

    Route::get('testimonials/create', [TestimonialController::class, 'create'])
        ->middleware('permission:testimonials.create,admin')
        ->name('testimonials.create');

    Route::post('testimonials', [TestimonialController::class, 'store'])
        ->middleware('permission:testimonials.create,admin')
        ->name('testimonials.store');

    Route::get('testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])
        ->middleware('permission:testimonials.update,admin')
        ->name('testimonials.edit');

    Route::put('testimonials/{testimonial}', [TestimonialController::class, 'update'])
        ->middleware('permission:testimonials.update,admin')
        ->name('testimonials.update');

    Route::post('testimonials/reorder', [TestimonialController::class, 'reorder'])
        ->middleware('permission:testimonials.update,admin')
        ->name('testimonials.reorder');

    Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy'])
        ->middleware('permission:testimonials.delete,admin')
        ->name('testimonials.destroy');
});
