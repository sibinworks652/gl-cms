<?php

namespace Modules\Settings\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SettingsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'admin';
        $permissions = [
            'settings.view',
            'settings.update',
            'settings.mail.update',
            'settings.general.update',
            'settings.system.update',
            'settings.admin.update',
            'settings.modules.update',
            'settings.ecommerce_settings.update',
            'settings.social.update',
            'settings.analytics.update',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        $systemAdminRole = Role::findOrCreate('Super Admin', $guard);
        $permissionModels = Permission::query()
            ->where('guard_name', $guard)
            ->whereIn('name', $permissions)
            ->get();

        foreach ($permissionModels as $permission) {
            if (! $systemAdminRole->permissions->contains('id', $permission->id)) {
                $systemAdminRole->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
