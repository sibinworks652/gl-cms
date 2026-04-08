<?php

namespace Modules\Email\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EmailPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'admin';

        $permissions = [
            'email.view',
            'email.settings.update',
            'email.templates.view',
            'email.templates.create',
            'email.templates.update',
            'email.templates.delete',
            'email.testing',
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
