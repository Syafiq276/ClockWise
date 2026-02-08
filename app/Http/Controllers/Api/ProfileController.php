<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     *
     * Return the authenticated user's full profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'profile' => [
                'id'                      => $user->id,
                'name'                    => $user->name,
                'email'                   => $user->email,
                'role'                    => $user->role,
                'position'                => $user->position,
                'hourly_rate'             => $user->hourly_rate,
                'annual_leave_entitlement' => $user->annual_leave_entitlement ?? 12,
                'mc_entitlement'          => $user->mc_entitlement ?? 14,
                'employment_start_date'   => $user->employment_start_date?->toDateString(),
                'created_at'              => $user->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * PUT /api/profile
     *
     * Update the authenticated user's profile details.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * PUT /api/profile/password
     *
     * Change the authenticated user's password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors'  => ['current_password' => ['The current password is incorrect.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
}
