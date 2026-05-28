<?php

/*
|--------------------------------------------------------------------------
| CityPulse Cashier overrides
|--------------------------------------------------------------------------
|
| Only the keys defined here override the package defaults that ship with
| laravel/cashier. We use a single Stripe price for the city subscription
| (80 EUR / month, no commitment). Configure STRIPE_PRICE_CITY_MONTHLY in
| your environment to point at the matching Stripe price ID.
|
*/

return [

    'currency' => env('CASHIER_CURRENCY', 'eur'),

    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'fr'),

    'plans' => [
        'city_monthly' => env('STRIPE_PRICE_CITY_MONTHLY', 'price_city_monthly'),
    ],

    'subscription' => [
        'amount' => 8000,
        'currency' => 'eur',
        'interval' => 'month',
        'label' => 'CityPulse - Commune',
    ],

];
