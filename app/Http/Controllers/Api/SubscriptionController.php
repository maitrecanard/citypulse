<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DiscordLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController;

/**
 * Controller for managing Stripe subscriptions.
 */
class SubscriptionController extends Controller
{
    /**
     * Show the current subscription status.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $city = $user->city;

            if (!$city) {
                return response()->json([
                    'message' => 'Aucune commune associee.',
                    'subscription' => null,
                ], 404);
            }

            return response()->json([
                'status' => $city->subscription_status,
                'city' => $city->only(['uuid', 'name', 'slug']),
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to show subscription', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Create a Stripe checkout session for subscribing.
     */
    public function subscribe(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->isMaire()) {
                return response()->json([
                    'message' => 'Seul le maire peut gerer l\'abonnement.',
                ], 403);
            }

            $city = $user->city;

            if (!$city) {
                return response()->json([
                    'message' => 'Aucune commune associee.',
                ], 404);
            }

            $checkout = $user->newSubscription('default', config('cashier.plans.city_monthly'))
                ->checkout([
                    'success_url' => config('app.url') . '/subscription/success?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => config('app.url') . '/subscription/cancel',
                ]);

            return response()->json([
                'checkout_url' => $checkout->url,
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to create checkout session', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Cancel the current subscription.
     */
    public function cancel(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->isMaire()) {
                return response()->json([
                    'message' => 'Seul le maire peut gerer l\'abonnement.',
                ], 403);
            }

            $city = $user->city;

            if (!$city) {
                return response()->json([
                    'message' => 'Aucune commune associee.',
                ], 404);
            }

            if ($user->subscription('default')) {
                $user->subscription('default')->cancel();

                $city->update(['subscription_status' => 'inactive']);

                app(DiscordLogger::class)->info('Subscription cancelled', [
                    'city' => $city->uuid,
                    'user' => $user->uuid,
                ]);
            }

            return response()->json([
                'message' => 'Abonnement annule avec succes.',
            ]);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Failed to cancel subscription', [
                'error' => $e->getMessage(),
                'user' => $request->user()?->uuid,
            ]);

            throw $e;
        }
    }

    /**
     * Handle incoming Stripe webhooks.
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            // Delegate to Cashier's webhook handler
            $controller = new WebhookController();

            return $controller->handleWebhook($request);
        } catch (\Throwable $e) {
            app(DiscordLogger::class)->error('Webhook handling failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
