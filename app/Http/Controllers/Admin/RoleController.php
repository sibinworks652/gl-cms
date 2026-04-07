<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::with('permissions')
            ->where('guard_name', 'admin')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($roles);
        }

        return view('admin.roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create(Request $request)
    {
        $permissions = Permission::query()
            ->where('guard_name', 'admin')
            ->orderBy('name')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'permissions' => $permissions,
            ]);
        }

        return view('admin.roles.form', [
            'role' => new Role(['guard_name' => 'admin']),
            'permissions' => $permissions,
            'permissionGroups' => $this->permissionGroups($permissions),
            'selectedPermissions' => [],
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('guard_name', 'admin')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'admin')],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'admin',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);
        $role->load('permissions');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Role created successfully.',
                'data' => $role,
            ], 201);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Request $request, Role $role)
    {
        abort_unless($role->guard_name === 'admin', 404);

        $role->load('permissions');
        $permissions = Permission::query()
            ->where('guard_name', 'admin')
            ->orderBy('name')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'role' => $role,
                'permissions' => $permissions,
            ]);
        }

        return view('admin.roles.form', [
            'role' => $role,
            'permissions' => $permissions,
            'permissionGroups' => $this->permissionGroups($permissions),
            'selectedPermissions' => $role->permissions->pluck('name')->all(),
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        abort_unless($role->guard_name === 'admin', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)->where('guard_name', 'admin')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'admin')],
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);
        $role->load('permissions');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Role updated successfully.',
                'data' => $role,
            ]);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Request $request, Role $role)
    {
        abort_unless($role->guard_name === 'admin', 404);

        $role->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Role deleted successfully.',
            ]);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    protected function permissionGroups(Collection $permissions): array
    {
        $actionOrder = [
            'view' => 10,
            'create' => 20,
            'update' => 30,
            'delete' => 40,
        ];

        return $permissions
            ->sortBy('name')
            ->groupBy(fn (Permission $permission) => Str::before($permission->name, '.'))
            ->map(function (Collection $groupPermissions, string $groupKey) use ($actionOrder) {
                return [
                    'label' => $this->permissionGroupLabel($groupKey),
                    'permissions' => $groupPermissions
                        ->sortBy(function (Permission $permission) use ($actionOrder) {
                            $action = Str::after($permission->name, '.');

                            return sprintf('%03d-%s', $actionOrder[$action] ?? 999, $action);
                        })
                        ->map(fn (Permission $permission) => [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'action' => $this->permissionActionLabel($permission->name),
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy('label')
            ->all();
    }

    protected function permissionGroupLabel(string $groupKey): string
    {
        $label = Str::headline(Str::singular($groupKey));

        return $label === 'Admin' ? 'Admin' : $label;
    }

    protected function permissionActionLabel(string $permissionName): string
    {
        return Str::headline(Str::after($permissionName, '.'));
    }
}
