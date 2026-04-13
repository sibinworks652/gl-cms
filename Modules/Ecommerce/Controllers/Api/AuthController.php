<?php

namespace Modules\Ecommerce\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::query()->create($validated);

        return response()->json([
            'message' => 'Registered successfully.',
            'user' => $user,
            'token' => method_exists($user, 'createToken') ? $user->createToken('ecommerce-api')->plainTextToken : null,
            'sanctum_ready' => method_exists($user, 'createToken'),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Auth::login($user);

        return response()->json([
            'message' => 'Logged in successfully.',
            'user' => $user,
            'token' => method_exists($user, 'createToken') ? $user->createToken('ecommerce-api')->plainTextToken : null,
            'sanctum_ready' => method_exists($user, 'createToken'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user() && method_exists($request->user(), 'currentAccessToken') && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        Auth::guard('web')->logout();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
