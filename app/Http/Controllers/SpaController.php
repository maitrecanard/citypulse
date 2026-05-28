<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Contracts\View\View;

/**
 * Serves the SPA shell.
 *
 * Renders the React entry point and injects per-city meta tags so that
 * each /ville/{uuid} page exposes a unique title, description, keywords
 * and Open Graph payload to search engines and social previews — the
 * SEO commitment described in the README.
 */
class SpaController extends Controller
{
    /**
     * Default SPA response (no city context).
     */
    public function index(): View
    {
        return view('app', [
            'title' => 'CityPulse - Gestion Communale',
            'metaDescription' => 'Plateforme SaaS de gestion communale : doleances, evenements, annonces, alertes et flotte de vehicules pour les villes et villages.',
            'metaKeywords' => 'commune, ville, doleances, evenements, annonces, alertes, gestion communale, citypulse, saas',
            'ogUrl' => url('/'),
            'ogImage' => url('/icons/icon-512.png'),
        ]);
    }

    /**
     * SPA response for the public city page (/ville/{uuid}).
     *
     * Looks the city up by UUID so server-rendered meta tags reflect the
     * actual city. Falls back to the default SPA payload when the UUID
     * does not exist so the React router can still render its 404.
     */
    public function city(string $uuid): View
    {
        $city = City::where('uuid', $uuid)->first();

        if (!$city) {
            return $this->index();
        }

        $title = $city->name . ' - CityPulse';
        $description = trim(($city->description ?: 'Decouvrez les actualites, evenements et alertes de ' . $city->name . '.')) . ' Service communal en ligne via CityPulse.';
        $keywords = collect([
            $city->name,
            $city->department,
            $city->region,
            'commune',
            'doleances',
            'evenements',
            'annonces',
            'alertes',
            'citypulse',
        ])->filter()->implode(', ');

        return view('app', [
            'title' => $title,
            'metaDescription' => $description,
            'metaKeywords' => $keywords,
            'ogUrl' => url('/ville/' . $city->uuid),
            'ogImage' => url('/icons/icon-512.png'),
            'cityName' => $city->name,
        ]);
    }
}
