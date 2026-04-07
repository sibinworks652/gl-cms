<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $permissions = Permission::query()
            ->where('guard_name', 'admin')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($permissions);
        }

        return view('admin.permissions.index', [
            'permissions' => $permissions,
        ]);
    }

    public function create(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'guard_name' => 'admin',
            ]);
        }

        return view('admin.permissions.form', [
            'permission' => new Permission(['guard_name' => 'admin']),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->where('guard_name', 'admin')],
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'admin',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Permission created successfully.',
                'data' => $permission,
            ], 201);
        }

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function edit(Request $request, Permission $permission)
    {
        abort_unless($permission->guard_name === 'admin', 404);

        if ($request->expectsJson()) {
            return response()->json([
                'permission' => $permission,
            ]);
        }

        return view('admin.permissions.form', [
            'permission' => $permission,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        abort_unless($permission->guard_name === 'admin', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permission->id)->where('guard_name', 'admin')],
        ]);

        $permission->update([
            'name' => $validated['name'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Permission updated successfully.',
                'data' => $permission,
            ]);
        }

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(Request $request, Permission $permission)
    {
        abort_unless($permission->guard_name === 'admin', 404);

        $permission->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Permission deleted successfully.',
            ]);
        }

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
