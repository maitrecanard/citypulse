<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Single catch-all route for the SPA frontend.
|
*/

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
