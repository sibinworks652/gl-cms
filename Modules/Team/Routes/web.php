<?php

use Illuminate\Support\Facades\Route;
use Modules\Team\Controllers\Admin\TeamDepartmentController;
use Modules\Team\Controllers\Admin\TeamMemberController;
use Modules\Team\Controllers\Web\TeamFrontController;

Route::get('team', [TeamFrontController::class, 'index'])
    ->name('team.index');

Route::get('team/{slug}', [TeamFrontController::class, 'show'])
    ->name('team.show');

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('team-members', [TeamMemberController::class, 'index'])
        ->middleware('permission:team-members.view,admin')
        ->name('team-members.index');

    Route::get('team-members/create', [TeamMemberController::class, 'create'])
        ->middleware('permission:team-members.create,admin')
        ->name('team-members.create');

    Route::post('team-members', [TeamMemberController::class, 'store'])
        ->middleware('permission:team-members.create,admin')
        ->name('team-members.store');

    Route::get('team-members/{team_member}/edit', [TeamMemberController::class, 'edit'])
        ->middleware('permission:team-members.update,admin')
        ->name('team-members.edit');

    Route::put('team-members/{team_member}', [TeamMemberController::class, 'update'])
        ->middleware('permission:team-members.update,admin')
        ->name('team-members.update');

    Route::post('team-members/reorder', [TeamMemberController::class, 'reorder'])
        ->middleware('permission:team-members.update,admin')
        ->name('team-members.reorder');

    Route::delete('team-members/{team_member}', [TeamMemberController::class, 'destroy'])
        ->middleware('permission:team-members.delete,admin')
        ->name('team-members.destroy');

    Route::get('team-departments', [TeamDepartmentController::class, 'index'])
        ->middleware('permission:team-departments.view,admin')
        ->name('team-departments.index');

    Route::get('team-departments/create', [TeamDepartmentController::class, 'create'])
        ->middleware('permission:team-departments.create,admin')
        ->name('team-departments.create');

    Route::post('team-departments', [TeamDepartmentController::class, 'store'])
        ->middleware('permission:team-departments.create,admin')
        ->name('team-departments.store');

    Route::get('team-departments/{team_department}/edit', [TeamDepartmentController::class, 'edit'])
        ->middleware('permission:team-departments.update,admin')
        ->name('team-departments.edit');

    Route::put('team-departments/{team_department}', [TeamDepartmentController::class, 'update'])
        ->middleware('permission:team-departments.update,admin')
        ->name('team-departments.update');

    Route::post('team-departments/reorder', [TeamDepartmentController::class, 'reorder'])
        ->middleware('permission:team-departments.update,admin')
        ->name('team-departments.reorder');

    Route::delete('team-departments/{team_department}', [TeamDepartmentController::class, 'destroy'])
        ->middleware('permission:team-departments.delete,admin')
        ->name('team-departments.destroy');
});
