<?php

namespace Modules\Ecommerce\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EcommercePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'admin';
        $adminPermissions = [
            'ecommerce.dashboard.view',
            'vendors.view',
            'vendors.create',
            'vendors.update',
            'vendors.delete',
            'product-categories.view',
            'product-categories.create',
            'product-categories.update',
            'product-categories.delete',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'orders.view',
            'orders.update',
        ];

        foreach ($adminPermissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        $role = Role::findOrCreate('Super Admin', $guard);

        foreach ($adminPermissions as $permission) {
            if (! $role->hasPermissionTo($permission, $guard)) {
                $role->givePermissionTo($permission);
            }
        }

        $adminRole = Role::findOrCreate('Admin', $guard);
        $viewPermissions = [
            'ecommerce.dashboard.view',
            'vendors.view',
            'product-categories.view',
            'products.view',
            'orders.view',
        ];
        foreach ($viewPermissions as $permission) {
            if (! $adminRole->hasPermissionTo($permission, $guard)) {
                $adminRole->givePermissionTo($permission);
            }
        }

        $webGuard = 'web';
        $vendorPermissions = [
            'vendor.dashboard.view',
            'vendor.products.view',
            'vendor.products.create',
            'vendor.products.update',
            'vendor.products.delete',
            'vendor.orders.view',
            'vendor.orders.update',
        ];

        foreach ($vendorPermissions as $permission) {
            Permission::findOrCreate($permission, $webGuard);
        }

        $vendorRole = Role::findOrCreate('vendor', $webGuard);
        foreach ($vendorPermissions as $permission) {
            if (! $vendorRole->hasPermissionTo($permission, $webGuard)) {
                $vendorRole->givePermissionTo($permission);
            }
        }

        // Keep compatibility if an existing "vendor" guard role/permissions are already in use.
        $legacyGuard = 'vendor';
        foreach ($vendorPermissions as $permission) {
            Permission::findOrCreate($permission, $legacyGuard);
        }

        $legacyVendorRole = Role::findOrCreate('vendor', $legacyGuard);
        foreach ($vendorPermissions as $permission) {
            if (! $legacyVendorRole->hasPermissionTo($permission, $legacyGuard)) {
                $legacyVendorRole->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
