<?php

namespace Modules\Page\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PagePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'admin';
        $permissions = [
            'pages.view',
            'pages.create',
            'pages.update',
            'pages.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        $systemAdminRole = Role::findOrCreate('Super Admin', $guard);

        foreach ($permissions as $permission) {
            $systemAdminRole->givePermissionTo(Permission::findByName($permission, $guard));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
