<?php

namespace Modules\FormBuilder\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FormBuilderPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'admin';
        $permissions = [
            'forms.view',
            'forms.create',
            'forms.update',
            'forms.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        $systemAdminRole = Role::findOrCreate('Super Admin', $guard);

        foreach ($permissions as $permission) {
            if (! $systemAdminRole->hasPermissionTo($permission, $guard)) {
                $systemAdminRole->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
