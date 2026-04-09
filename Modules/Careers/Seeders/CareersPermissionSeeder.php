<?php

namespace Modules\Careers\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CareersPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'admin';
        $permissions = [
            'careers.jobs.view',
            'careers.jobs.create',
            'careers.jobs.update',
            'careers.jobs.delete',
            'careers.categories.view',
            'careers.categories.create',
            'careers.categories.update',
            'careers.categories.delete',
            'careers.applications.view',
            'careers.applications.update',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        $role = Role::findOrCreate('Super Admin', $guard);

        foreach ($permissions as $permission) {
            $permissionModel = Permission::findByName($permission, $guard);

            if (! $role->permissions->contains('name', $permission)) {
                $role->givePermissionTo($permissionModel);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
