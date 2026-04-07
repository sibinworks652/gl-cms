<?php

namespace Modules\Backup\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BackupPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'admin';
        $permissions = [
            'backups.view',
            'backups.create',
            'backups.delete',
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
