<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Support\ModuleRegistry;
use Illuminate\Support\Facades\Route;
use Modules\Banner\Controllers\BannerController;

if (ModuleRegistry::enabled('banner')) {
    Route::prefix('modules')->name('api.modules.')->group(function () {
        Route::get('banners', [BannerController::class, 'apiPublicIndex'])->name('banners.index');
        Route::get('banners/{banner}', [BannerController::class, 'apiPublicShow'])->name('banners.show');
    });
}

Route::prefix('admin')->name('api.admin.')->group(function () {
    if (ModuleRegistry::enabled('banner')) {
        Route::get('banners', [BannerController::class, 'index'])
            ->middleware('permission:banners.view,admin')
            ->name('banners.index');
        Route::get('banners/create', [BannerController::class, 'create'])
            ->middleware('permission:banners.create,admin')
            ->name('banners.create');
        Route::post('banners', [BannerController::class, 'store'])
            ->middleware('permission:banners.create,admin')
            ->name('banners.store');
        Route::get('banners/{banner}/edit', [BannerController::class, 'edit'])
            ->middleware('permission:banners.update,admin')
            ->name('banners.edit');
        Route::put('banners/{banner}', [BannerController::class, 'update'])
            ->middleware('permission:banners.update,admin')
            ->name('banners.update');
        Route::post('banners/reorder', [BannerController::class, 'reorder'])
            ->middleware('permission:banners.update,admin')
            ->name('banners.reorder');
        Route::delete('banners/{banner}', [BannerController::class, 'destroy'])
            ->middleware('permission:banners.delete,admin')
            ->name('banners.destroy');
    }

    Route::get('admins', [AdminController::class, 'index'])
        ->middleware('permission:admins.view,admin')
        ->name('admins.index');
    Route::get('admins/create', [AdminController::class, 'create'])
        ->middleware('permission:admins.create,admin')
        ->name('admins.create');
    Route::post('admins', [AdminController::class, 'store'])
        ->middleware('permission:admins.create,admin')
        ->name('admins.store');
    Route::get('admins/{admin}/edit', [AdminController::class, 'edit'])
        ->middleware('permission:admins.update,admin')
        ->name('admins.edit');
    Route::put('admins/{admin}', [AdminController::class, 'update'])
        ->middleware('permission:admins.update,admin')
        ->name('admins.update');
    Route::delete('admins/{admin}', [AdminController::class, 'destroy'])
        ->middleware('permission:admins.delete,admin')
        ->name('admins.destroy');

    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view,admin')
        ->name('roles.index');
    Route::get('roles/create', [RoleController::class, 'create'])
        ->middleware('permission:roles.create,admin')
        ->name('roles.create');
    Route::post('roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.create,admin')
        ->name('roles.store');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:roles.update,admin')
        ->name('roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.update,admin')
        ->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete,admin')
        ->name('roles.destroy');

    Route::get('permissions', [PermissionController::class, 'index'])
        ->middleware('permission:permissions.view,admin')
        ->name('permissions.index');
    Route::get('permissions/create', [PermissionController::class, 'create'])
        ->middleware('permission:permissions.create,admin')
        ->name('permissions.create');
    Route::post('permissions', [PermissionController::class, 'store'])
        ->middleware('permission:permissions.create,admin')
        ->name('permissions.store');
    Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])
        ->middleware('permission:permissions.update,admin')
        ->name('permissions.edit');
    Route::put('permissions/{permission}', [PermissionController::class, 'update'])
        ->middleware('permission:permissions.update,admin')
        ->name('permissions.update');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])
        ->middleware('permission:permissions.delete,admin')
        ->name('permissions.destroy');
});
