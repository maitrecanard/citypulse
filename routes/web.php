<?php

use App\Http\Controllers\SpaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The SPA entry point. The /ville/{uuid} route is rendered with city-aware
| meta tags (Open Graph, description, keywords) so each commune has a
| unique, indexable preview while the React app still drives the UI.
|
*/

Route::get('/ville/{uuid}', [SpaController::class, 'city'])
    ->where('uuid', '[0-9a-fA-F-]{36}');

Route::get('/{any?}', [SpaController::class, 'index'])
    ->where('any', '.*');
