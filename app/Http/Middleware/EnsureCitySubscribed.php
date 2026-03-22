<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure the authenticated user's city has an active subscription.
 *
 * Skips the check for administres accessing their own profile or doleances.
 */
class EnsureCitySubscribed
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifie.'], 401);
        }

        // Skip subscription check for administres accessing their profile or doleances
        if ($user->isAdministre()) {
            $path = $request->path();
            if (str_contains($path, 'profile') || str_contains($path, 'doleances')) {
                return $next($request);
            }
        }

        // Check if user has a city and if it has an active subscription
        if (!$user->city || !$user->city->hasActiveSubscription()) {
            return response()->json([
                'message' => 'Votre commune ne dispose pas d\'un abonnement actif.',
                'subscription_required' => true,
            ], 403);
        }

        return $next($request);
    }
}
