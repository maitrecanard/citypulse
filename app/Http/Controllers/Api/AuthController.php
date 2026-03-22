<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DiscordLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Controller for authentication operations.
 */
class AuthController extends Controller
{
    /**
     * Authenticate a user and return a session.
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (!Auth::attempt($validated, $request->boolean('remember'))) {
                return response()->json([
                    'message' => 'Identifiants incorrects.',
                ], 401);
            }

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $user = Auth::user();

            return response()->json([
                'message' => 'Connexion reussie.',
                'user' => $user->load('city'),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Login failed', [
                'error' => $e->getMessage(),
                'email' => $request->input('email'),
            ]);

            throw $e;
        }
    }

    /**
     * Register a new user account.
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::defaults()],
                'phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:1000'],
                'city_uuid' => ['nullable', 'exists:cities,uuid'],
            ]);

            $cityId = null;
            if (!empty($validated['city_uuid'])) {
                $cityId = \App\Models\City::where('uuid', $validated['city_uuid'])->value('id');
            }

            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'role' => 'administre',
                'city_id' => $cityId,
            ]);

            Auth::login($user);
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            app(DiscordLogger::class)->info('New user registered', [
                'user' => $user->uuid,
                'email' => $user->email,
            ]);

            return response()->json([
                'message' => 'Inscription reussie.',
                'user' => $user->load('city'),
            ], 201);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Registration failed', [
                'error' => $e->getMessage(),
                'email' => $request->input('email'),
            ]);

            throw $e;
        }
    }

    /**
     * Log out the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Deconnexion reussie.',
        ]);
    }

    /**
     * Return the currently authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('city'),
        ]);
    }
}
