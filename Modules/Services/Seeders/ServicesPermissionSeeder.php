<?php

namespace Modules\Services\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ServicesPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'admin';
        $permissions = [
            'services.view',
            'services.create',
            'services.update',
            'services.delete',
            'service-categories.view',
            'service-categories.create',
            'service-categories.update',
            'service-categories.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        $role = Role::findOrCreate('Super Admin', $guard);

        foreach ($permissions as $permission) {
            if (! $role->hasPermissionTo($permission, $guard)) {
                $role->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
