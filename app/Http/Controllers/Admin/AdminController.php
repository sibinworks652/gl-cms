<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $admins = Admin::with('roles')->latest()->paginate(10)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($admins);
        }

        return view('admin.admin.index', [
            'admins' => $admins,
        ]);
    }

    public function create(Request $request)
    {
        $roles = Role::query()
            ->where('guard_name', 'admin')
            ->orderBy('name')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'roles' => $roles,
            ]);
        }

        return view('admin.admin.form', [
            'admin' => new Admin(),
            'roles' => $roles,
            'selectedRoles' => [],
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:admins,username'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'admin')],
        ]);

        $admin = Admin::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        $admin->syncRoles($validated['roles'] ?? []);
        $admin->load('roles');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Admin created successfully.',
                'data' => $admin,
            ], 201);
        }

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin created successfully.');
    }

    public function edit(Request $request, Admin $admin)
    {
        $admin->load('roles');
        $roles = Role::query()
            ->where('guard_name', 'admin')
            ->orderBy('name')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'admin' => $admin,
                'roles' => $roles,
            ]);
        }

        return view('admin.admin.form', [
            'admin' => $admin,
            'roles' => $roles,
            'selectedRoles' => $admin->roles->pluck('name')->all(),
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('admins', 'username')->ignore($admin->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'admin')],
        ]);

        $payload = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $admin->update($payload);
        $admin->syncRoles($validated['roles'] ?? []);
        $admin->load('roles');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Admin updated successfully.',
                'data' => $admin,
            ]);
        }

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    public function destroy(Request $request, Admin $admin)
    {
        $admin->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Admin deleted successfully.',
            ]);
        }

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }
}
