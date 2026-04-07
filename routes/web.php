<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

/*********************
 * Admin Routes
 *********************/
Route::get('/', [AuthController::class, 'showLoginForm']);
Route::view('/menu-preview', 'menu-preview')->name('menu.preview');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view,admin')
        ->name('dashboard');

    Route::get('/admins', [AdminController::class, 'index'])
        ->middleware('permission:admins.view,admin')
        ->name('admins.index');
    Route::get('/admins/create', [AdminController::class, 'create'])
        ->middleware('permission:admins.create,admin')
        ->name('admins.create');
    Route::post('/admins', [AdminController::class, 'store'])
        ->middleware('permission:admins.create,admin')
        ->name('admins.store');
    Route::get('/admins/{admin}/edit', [AdminController::class, 'edit'])
        ->middleware('permission:admins.update,admin')
        ->name('admins.edit');
    Route::put('/admins/{admin}', [AdminController::class, 'update'])
        ->middleware('permission:admins.update,admin')
        ->name('admins.update');
    Route::delete('/admins/{admin}', [AdminController::class, 'destroy'])
        ->middleware('permission:admins.delete,admin')
        ->name('admins.destroy');

    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view,admin')
        ->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])
        ->middleware('permission:roles.create,admin')
        ->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.create,admin')
        ->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:roles.update,admin')
        ->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.update,admin')
        ->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete,admin')
        ->name('roles.destroy');

    Route::get('/permissions', [PermissionController::class, 'index'])
        ->middleware('permission:permissions.view,admin')
        ->name('permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])
        ->middleware('permission:permissions.create,admin')
        ->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])
        ->middleware('permission:permissions.create,admin')
        ->name('permissions.store');
    Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])
        ->middleware('permission:permissions.update,admin')
        ->name('permissions.edit');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])
        ->middleware('permission:permissions.update,admin')
        ->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
        ->middleware('permission:permissions.delete,admin')
        ->name('permissions.destroy');
});
