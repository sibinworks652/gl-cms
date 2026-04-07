<?php

namespace Modules\Gallery\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GalleryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'admin';

        $permissions = [
            'gallery.view',
            'gallery.create',
            'gallery.update',
            'gallery.delete',
        ];

        // Create Permissions
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        $systemAdminRole = Role::findOrCreate('Super Admin', $guard);

        foreach ($permissions as $permission) {
            if (! $systemAdminRole->hasPermissionTo($permission, $guard)) {
                $systemAdminRole->givePermissionTo($permission);
            }
        }

        // Clear cache again to ensure changes take effect immediately
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
