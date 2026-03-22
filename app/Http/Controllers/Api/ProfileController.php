<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DiscordLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Controller for user profile management.
 */
class ProfileController extends Controller
{
    /**
     * Show the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('city'),
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'first_name' => ['sometimes', 'string', 'max:255'],
                'last_name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'email', 'unique:users,email,' . $request->user()->id],
                'phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:1000'],
            ]);

            $user = $request->user();

            // Update the name field to match first_name + last_name
            if (isset($validated['first_name']) || isset($validated['last_name'])) {
                $firstName = $validated['first_name'] ?? $user->first_name;
                $lastName = $validated['last_name'] ?? $user->last_name;
                $validated['name'] = $firstName . ' ' . $lastName;
            }

            $user->update($validated);

            return response()->json([
                'message' => 'Profil mis a jour avec succes.',
                'user' => $user->fresh()->load('city'),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to update profile', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'message' => 'Mot de passe mis a jour avec succes.',
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to update password', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }
}
