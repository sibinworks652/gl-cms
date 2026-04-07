<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'admin';
        $permissions = [
            'dashboard.view',
            'admins.view',
            'admins.create',
            'admins.update',
            'admins.delete',
            'admins.restore',
            'admins.status',
            'admins.password',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
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

        $admin = Admin::firstOrCreate(
            ['email' => 'admin@gl-cms.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('Admin@12345'),
                'is_active' => true,
            ]
        );

        if (! $admin->hasRole($systemAdminRole)) {
            $admin->assignRole($systemAdminRole);
        }

        if (! $admin->is_active) {
            $admin->forceFill(['is_active' => true])->save();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
