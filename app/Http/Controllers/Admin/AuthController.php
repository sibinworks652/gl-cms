<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ModuleRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);

        if (! Auth::guard('admin')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials or inactive admin account.',
            ]);
        }

        $request->session()->regenerate();
        $this->activityLogs()?->recordLogin(Auth::guard('admin')->user(), $request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login successful.',
                'redirect' => route('admin.dashboard'),
            ]);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            $this->activityLogs()?->recordLogout($admin, $request);
        }

        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Logout successful.',
                'redirect' => route('login'),
            ]);
        }

        return redirect()->route('login');
    }

    protected function activityLogs(): ?object
    {
        $class = \Modules\ActivityLogs\Services\ActivityLogManager::class;

        if (! ModuleRegistry::enabled('activity_logs') || ! class_exists($class)) {
            return null;
        }

        return app($class);
    }
}
