<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription ?? 'CityPulse - Plateforme de gestion communale pour les villes et villages. Doléances, événements, annonces et alertes.' }}">
    <meta name="keywords" content="{{ $metaKeywords ?? 'commune, ville, doléances, événements, gestion communale, citypulse' }}">
    <meta name="theme-color" content="#2563eb">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="canonical" href="{{ $ogUrl ?? url()->current() }}">
    <title>{{ $title ?? 'CityPulse - Gestion Communale' }}</title>

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $title ?? 'CityPulse - Gestion Communale' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Plateforme de gestion communale pour les villes et villages.' }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:url" content="{{ $ogUrl ?? url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? url('/icons/icon-512.png') }}">
    <meta property="og:site_name" content="CityPulse">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'CityPulse - Gestion Communale' }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? 'Plateforme de gestion communale pour les villes et villages.' }}">
    <meta name="twitter:image" content="{{ $ogImage ?? url('/icons/icon-512.png') }}">

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body class="antialiased bg-surface text-gray-900">
    <div id="app"></div>

    @isset($cityName)
        <noscript>
            <main style="padding:2rem;font-family:sans-serif">
                <h1>{{ $cityName }}</h1>
                <p>{{ $metaDescription }}</p>
                <p>Activez JavaScript pour acceder a l'application CityPulse de {{ $cityName }}.</p>
            </main>
        </noscript>
    @endisset

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>
</html>
