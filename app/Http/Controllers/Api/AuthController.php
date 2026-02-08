<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * POST /api/login
     *
     * Authenticate user and return a Sanctum bearer token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        $deviceName = $validated['device_name'] ?? ($request->header('User-Agent') ?: 'api-client');

        // Revoke previous tokens for the same device to prevent token bloat
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => $this->userPayload($user),
        ]);
    }

    /**
     * POST /api/register
     *
     * Create a new employee account and return a token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'unique:users,email'],
            'password'    => ['required', 'confirmed', Password::min(8)],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'employee',
        ]);

        $deviceName = $validated['device_name'] ?? ($request->header('User-Agent') ?: 'api-client');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'token'   => $token,
            'user'    => $this->userPayload($user),
        ], 201);
    }

    /**
     * POST /api/logout
     *
     * Revoke the current bearer token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * GET /api/me
     *
     * Return the authenticated user's details.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    /**
     * Format a consistent user payload for API responses.
     */
    private function userPayload(User $user): array
    {
        return [
            'id'                   => $user->id,
            'name'                 => $user->name,
            'email'                => $user->email,
            'role'                 => $user->role,
            'position'             => $user->position,
            'hourly_rate'          => $user->hourly_rate,
            'employment_start_date' => $user->employment_start_date?->toDateString(),
            'created_at'           => $user->created_at->toIso8601String(),
        ];
    }
}
