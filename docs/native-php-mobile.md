# CityPulse — App mobile native (NativePHP for Mobile)

CityPulse embarque les hooks nécessaires pour générer une vraie app iOS et
Android via [NativePHP for Mobile](https://nativephp.com/docs/mobile/1). Le
Laravel + React existants tournent directement à l'intérieur du binaire
mobile, sans réécriture du front.

> NativePHP for Mobile est un **package commercial sous licence payante**. Le
> dépôt CityPulse ne vendorise donc pas la dépendance ; cette page décrit la
> procédure d'installation à réaliser une fois la licence achetée.

## Pré-requis

| Outil | Version min. | Pourquoi |
|-------|--------------|----------|
| PHP CLI | 8.3 | Conforme au reste du projet Laravel. |
| Composer | 2.x | Pour télécharger le package depuis `nativephp.composer.sh`. |
| Node.js | 20.x | Pour `npm run build` (assets Vite). |
| Android Studio + SDK | API 34+ | Build / run sur Android. |
| Xcode + iOS SDK | 16+ | Build / run sur iOS (macOS uniquement). |
| Apple Developer Team ID | — | Renseigné dans `NATIVEPHP_DEVELOPMENT_TEAM`. |
| Licence NativePHP | — | Email + clé reçus à l'achat. |

Activez les options développeur sur vos appareils :

- **Android** : *Developer options* + *USB debugging* activés, `adb devices`
  doit lister l'appareil.
- **iOS** : *Developer Mode* activé (Réglages → Confidentialité & sécurité →
  Mode Développeur) + appareil ajouté à votre compte Apple Developer.

## Installation

```bash
# 1. Le repo Composer privé est déjà déclaré dans composer.json :
#    "repositories": [{"type":"composer","url":"https://nativephp.composer.sh"}]
composer require nativephp/mobile
# Composer demandera votre email + clé de licence.

# 2. Génère les artefacts iOS/Android et la configuration.
php artisan native:install
# Choisissez les binaires ICU-PHP si vous activez `ext-intl` ou Filament.
```

Vérifiez ensuite `.env` :

```dotenv
NATIVEPHP_APP_ID=fr.citypulse.app
NATIVEPHP_APP_VERSION=1.0.0
NATIVEPHP_APP_VERSION_CODE=1
NATIVEPHP_DEVELOPMENT_TEAM=ABCDE12345    # Apple Team ID
NATIVEPHP_DEEPLINKING_ENABLED=true
NATIVEPHP_DEEPLINKING_SCHEME=citypulse
```

## Lancer l'app

```bash
# Build des assets web (utilisés à l'intérieur du binaire)
npm run build

# Démarrage sur un appareil connecté ; le CLI demande la cible (iOS / Android)
php artisan native:run
```

Le service worker et le manifest PWA déjà présents (`public/sw.js`,
`public/manifest.json`) restent utilisés : la couche NativePHP ne fait que
remplacer le navigateur par une coque native.

## Spécificités CityPulse

- **Authentification** : NativePHP exécute le Laravel localement, donc les
  cookies httponly de Sanctum continuent de fonctionner sans changement.
  Aucune API token n'est nécessaire côté mobile.
- **Notifications push** : se brancher sur les helpers
  `Native\Mobile\Facades\Notification` (voir docs NativePHP) ; les événements
  CityPulse pertinents (nouvelle annonce, alerte critique) sont déjà loggués
  via `DiscordLogger` et peuvent être relayés.
- **Caméra / pièces jointes** : le formulaire de doléance peut ouvrir la
  caméra via `Native\Mobile\Facades\Camera::capture()`. Les fichiers sont
  ensuite envoyés à `POST /api/doleances` via le client Axios existant.
- **Stripe** : l'abonnement reste géré côté maire depuis l'UI web ;
  l'app mobile administré n'a pas besoin de checkout natif.

## Distribution

```bash
# Build de release prêt à publier
php artisan native:build android
php artisan native:build ios
```

Les artefacts (`.apk`, `.aab`, `.ipa`) atterrissent dans `nativephp/dist/` —
ce dossier est gitignoré.

## Dépannage

- *`Authentication required for nativephp.composer.sh`* : la clé de licence
  est lue dans `auth.json` (commande `composer config --global --auth ...`).
  Ne committez jamais ce fichier (`auth.json` est déjà dans `.gitignore`).
- *Build Android échoue avec `SDK location not found`* : vérifiez
  `$ANDROID_HOME` ou le fichier `local.properties` généré par
  `native:install`.
- *Build iOS échoue sur le signing* : `NATIVEPHP_DEVELOPMENT_TEAM` doit
  correspondre exactement au Team ID Apple Developer.
