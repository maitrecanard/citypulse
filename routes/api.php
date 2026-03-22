<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CityPublicController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DoleanceController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\InterventionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All API routes for the CityPulse application.
| Routes use UUID for model binding - database IDs are never exposed.
|
*/

// Public authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Public city information
Route::get('/cities/{uuid}', [CityPublicController::class, 'show']);
Route::get('/cities/{uuid}/public', [CityPublicController::class, 'show']);

// Stripe webhook (no auth required)
Route::post('/stripe/webhook', [SubscriptionController::class, 'webhook']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // Subscription management
    Route::get('/subscription', [SubscriptionController::class, 'show']);
    Route::post('/subscription', [SubscriptionController::class, 'subscribe']);
    Route::delete('/subscription', [SubscriptionController::class, 'cancel']);

    // Resource routes (protected by policies within controllers)
    Route::apiResource('doleances', DoleanceController::class);
    Route::apiResource('events', EventController::class);
    Route::apiResource('announcements', AnnouncementController::class);
    Route::apiResource('alerts', AlertController::class);
    Route::apiResource('interventions', InterventionController::class);
    Route::apiResource('vehicles', VehicleController::class);

    // Vehicle maintenance routes
    Route::get('/vehicles/{vehicle}/maintenances', [VehicleController::class, 'maintenances']);
    Route::post('/vehicles/{vehicle}/maintenances', [VehicleController::class, 'addMaintenance']);
});
