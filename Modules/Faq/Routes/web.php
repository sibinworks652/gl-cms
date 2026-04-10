<?php

use Illuminate\Support\Facades\Route;
use Modules\Faq\Controllers\Admin\FaqCategoryController;
use Modules\Faq\Controllers\Admin\FaqController;
use Modules\Faq\Controllers\Web\FaqFrontController;

Route::get('faq', [FaqFrontController::class, 'index'])
    ->name('faq.index');

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('faqs', [FaqController::class, 'index'])
        ->middleware('permission:faqs.view,admin')
        ->name('faqs.index');

    Route::get('faqs/create', [FaqController::class, 'create'])
        ->middleware('permission:faqs.create,admin')
        ->name('faqs.create');

    Route::post('faqs', [FaqController::class, 'store'])
        ->middleware('permission:faqs.create,admin')
        ->name('faqs.store');

    Route::get('faqs/{faq}/edit', [FaqController::class, 'edit'])
        ->middleware('permission:faqs.update,admin')
        ->name('faqs.edit');

    Route::put('faqs/{faq}', [FaqController::class, 'update'])
        ->middleware('permission:faqs.update,admin')
        ->name('faqs.update');

    Route::post('faqs/reorder', [FaqController::class, 'reorder'])
        ->middleware('permission:faqs.update,admin')
        ->name('faqs.reorder');

    Route::delete('faqs/{faq}', [FaqController::class, 'destroy'])
        ->middleware('permission:faqs.delete,admin')
        ->name('faqs.destroy');

    Route::get('faq-categories', [FaqCategoryController::class, 'index'])
        ->middleware('permission:faq-categories.view,admin')
        ->name('faq-categories.index');

    Route::get('faq-categories/create', [FaqCategoryController::class, 'create'])
        ->middleware('permission:faq-categories.create,admin')
        ->name('faq-categories.create');

    Route::post('faq-categories', [FaqCategoryController::class, 'store'])
        ->middleware('permission:faq-categories.create,admin')
        ->name('faq-categories.store');

    Route::get('faq-categories/{faq_category}/edit', [FaqCategoryController::class, 'edit'])
        ->middleware('permission:faq-categories.update,admin')
        ->name('faq-categories.edit');

    Route::put('faq-categories/{faq_category}', [FaqCategoryController::class, 'update'])
        ->middleware('permission:faq-categories.update,admin')
        ->name('faq-categories.update');

    Route::post('faq-categories/reorder', [FaqCategoryController::class, 'reorder'])
        ->middleware('permission:faq-categories.update,admin')
        ->name('faq-categories.reorder');

    Route::delete('faq-categories/{faq_category}', [FaqCategoryController::class, 'destroy'])
        ->middleware('permission:faq-categories.delete,admin')
        ->name('faq-categories.destroy');
});
