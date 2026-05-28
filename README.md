# CityPulse

## Description
CityPulse est un systeme SaaS pour les villes et villages. Il permet la creation et la gestion des doleances par les administres, prises en charge par les communes. Il permet egalement aux agents communaux la gestion des evenements, annonces, alertes de voisinage, ainsi que la gestion des interventions des agents de terrain et de la flotte de vehicules.

C'est un service cle en main, complet, qui fonctionne en PWA sur tout support. Les administres peuvent facilement communiquer avec leurs elus et les elus peuvent gerer l'ensemble des agents, vehicules et interventions.

## Stack
- Laravel Vite monolithique
- React 19
- Redis
- Tailwind CSS v4
- Discord (logging)
- Stripe (abonnements via Laravel Cashier)
- Laravel Sanctum (authentification httponly)

## SEO
Chaque ville enregistree possede une URL dediee (`/ville/:uuid`), permettant aux administres de retrouver le site simplement et aux moteurs de recherche de referencer efficacement le systeme.
- Route Laravel dediee `/ville/{uuid}` resolue cote serveur par `SpaController::city`
- Meta tags dynamiques par ville injectees dans `app.blade.php` (title, description, keywords)
- Open Graph + Twitter Card par ville (`og:url`, `og:image`, `og:site_name`, `twitter:card`)
- Lien canonique par page (`<link rel="canonical">`)
- Bloc `<noscript>` SEO-friendly mentionnant la commune pour les crawlers sans JS
- Manifest PWA avec icones
- Service Worker pour le cache et le mode hors-ligne

## Securite
- Connexion securisee avec cookies httponly (Laravel Sanctum stateful)
- Les donnees sensibles (password, remember_token, stripe_id, pm_type, pm_last_four, trial_ends_at, stripe_subscription_id) ne sont jamais exposees dans les reponses API
- Les IDs de base de donnees ne sont jamais exposes au frontend ; les liens utilisent des UUID
- Un administre ne peut pas modifier une ville, il peut uniquement creer une doleance, la modifier, ou la supprimer si celle-ci n'est pas encore consultee par l'administration
- Un administre peut modifier uniquement une doleance dont il est proprietaire
- Un administre peut uniquement modifier son profil, son mot de passe, ses informations personnelles
- Les agents (maire, secretaire, agent communaux) ont des permissions strictes par role et par ville
- Policies Laravel pour chaque entite avec autorisation granulaire (DoleancePolicy, EventPolicy, AnnouncementPolicy, AlertPolicy, InterventionPolicy, VehiclePolicy)

## Gestion des vehicules
- Gestion des vehicules par equipe (voiture, camion, utilitaire, engin)
- Gestion de l'entretien des vehicules avec historique complet (`VehicleMaintenance`)
- Suivi des prochaines maintenances (`next_maintenance_at` mis a jour automatiquement a l'ajout d'une maintenance)

## Charte graphique
- Design moderne et dynamique avec Tailwind CSS v4
- Gradients, transitions, hover effects
- Interface responsive mobile-first
- Composants UI reutilisables (Button, Card, Modal, Alert, Badge, Input)
- Theme personnalise avec variables CSS (primary, secondary, accent, danger)

## Exigences
- Toutes les icones sont fonctionnelles (SVG inline)
- Logs Discord integres dans tous les controllers (try/catch + actions importantes)
- Tests unitaires complets : **194 tests, 490 assertions, 0 echec**

## Abonnement
Abonnement a tarif unique de 80EUR par mois sans engagement, gere via Stripe / Laravel Cashier.
- Plan configurable via `config/cashier.php` (cle `plans.city_monthly`)
- Prix Stripe injecte via la variable d'environnement `STRIPE_PRICE_CITY_MONTHLY`
- Devise par defaut `EUR`, locale `fr`
- Webhook Stripe gere par `SubscriptionController::webhook` (delegue a `Laravel\Cashier\Http\Controllers\WebhookController`)
- Seul le maire peut souscrire ou annuler l'abonnement

---

## Rapport d'implementation

### Cycle qualite
Le repo est passe par le cycle complet : **developpement -> verification -> tests unitaires -> execution -> correction -> regression**. Les deux ecarts identifies entre la specification et le code livre ont ete corriges dans cette passe :

| Ecart identifie | Statut | Correction |
|-----------------|--------|------------|
| `web.php` etait un simple catch-all : les meta tags `/ville/:uuid` n'etaient jamais peuples cote serveur | **Corrige** | Ajout de `SpaController` (index + city), route dediee `/ville/{uuid}` avec contrainte UUID, blade enrichie (canonical, OG complet, Twitter Card, `<noscript>` SEO) |
| `SubscriptionController` referencait `config('cashier.plans.city_monthly')` mais il n'existait aucun `config/cashier.php` ; les variables Stripe absentes de `.env.example` | **Corrige** | Creation de `config/cashier.php` (currency EUR, plan `city_monthly`, montant 8000 cents) + ajout des variables `STRIPE_KEY/SECRET/WEBHOOK_SECRET/PRICE_CITY_MONTHLY` et `SANCTUM_STATEFUL_DOMAINS` a `.env.example` |

Aucune regression : les 187 tests existants restent verts, 7 nouveaux tests ont ete ajoutes (4 SEO + 3 config Cashier), pour un total de **194 tests / 490 assertions / 0 echec** en `3.90s`.

### Architecture

```
citypulse/
  app/
    Http/Controllers/
      Controller.php
      SpaController.php           # << NOUVEAU - SSR meta tags /ville/{uuid}
      Api/                        # 11 controllers API
    Models/                       # 10 modeles Eloquent
    Policies/                     # 6 policies d'autorisation
    Services/                     # DiscordLogger
    Traits/                       # HasUuid
    Http/Middleware/              # EnsureCitySubscribed
  config/
    cashier.php                   # << NOUVEAU - plan unique 80EUR/mois
  database/
    factories/                    # 9 factories
    migrations/                   # 10 migrations custom + Sanctum + Cashier
  resources/
    js/
      app.jsx                     # Point d'entree React avec routing
      components/                 # 28 composants React
      contexts/                   # AuthContext
      hooks/                      # useApi
      services/                   # API Axios
    css/app.css                   # Tailwind v4 avec theme custom
    views/app.blade.php           # Template SPA + meta tags dynamiques
  routes/
    api.php                       # Routes API (auth, doleances, events, ...)
    web.php                       # Route /ville/{uuid} + SPA catch-all
  public/
    manifest.json                 # PWA manifest
    sw.js                         # Service Worker (network-first + cache)
    icons/                        # Icones PWA 192x192 / 512x512
  tests/
    Unit/
      Config/                     # << NOUVEAU - Cashier config
      Models/                     # User, City, Doleance
      Services/                   # DiscordLogger
    Feature/
      Auth/ Doleance/ Event/ Announcement/ Alert/
      Intervention/ Vehicle/ Profile/ Dashboard/ City/
      Seo/                        # << NOUVEAU - SSR /ville/{uuid}
```

### Modeles & Migrations
| Modele | Table | Description |
|--------|-------|-------------|
| User | users | Utilisateurs avec roles, UUID, relations ville, Billable (Cashier) |
| City | cities | Communes avec abonnement Stripe (`subscription_status`) |
| Doleance | doleances | Doleances citoyens avec statut et reponse admin |
| Event | events | Evenements communaux (publies / brouillons) |
| Announcement | announcements | Annonces officielles avec priorite |
| Alert | alerts | Alertes de voisinage avec severite et expiration |
| Intervention | interventions | Interventions terrain avec agent et vehicule |
| Service | services | Services communaux |
| Vehicle | vehicles | Flotte de vehicules par equipe |
| VehicleMaintenance | vehicle_maintenances | Historique d'entretien vehicules |

### Roles & Permissions
| Role | Doleances | Evenements | Annonces | Alertes | Interventions | Vehicules | Abonnement |
|------|-----------|------------|----------|---------|---------------|-----------|------------|
| Administre | CRUD propres | Lecture | Lecture | Lecture | - | - | - |
| Agent | Lecture ville | CRUD | CRUD | CRUD | CRUD | Lecture | - |
| Secretaire | Lecture ville | CRUD | CRUD | CRUD | CRUD | CRUD | - |
| Maire | Lecture ville | CRUD | CRUD | CRUD | CRUD | CRUD | Gestion |

### API Routes
- **Auth** : `POST /api/login`, `/api/register`, `/api/logout` ; `GET /api/user`
- **Doleances** : CRUD complet avec filtrage par role
- **Evenements** : CRUD (staff) + lecture (tous)
- **Annonces** : CRUD (staff) + lecture (tous)
- **Alertes** : CRUD (staff) + lecture (tous)
- **Interventions** : CRUD (staff uniquement)
- **Vehicules** : CRUD (maire/secretaire) + maintenances
- **Profil** : `GET/PUT /api/profile`, `PUT /api/profile/password`
- **Abonnement** : `GET/POST/DELETE /api/subscription` + webhook Stripe
- **Ville publique (API)** : `GET /api/cities/{uuid}` et `GET /api/cities/{uuid}/public`
- **Dashboard** : `GET /api/dashboard` (stats par role)
- **SEO web** : `GET /ville/{uuid}` (SSR meta tags), catch-all SPA

### Tests
```
Tests:    194 passed (490 assertions)
Duration: 3.90s

Unit Tests (49 tests):
  - UserTest: UUID, roles, relations, hidden fields, password hash
  - CityTest: UUID, relations, soft deletes, subscription
  - DoleanceTest: UUID, fillable, hidden, casts, relations
  - DiscordLoggerTest: instantiation, HTTP mocking, error handling
  - CashierConfigTest: currency EUR, plan city_monthly, montant 80EUR  # NOUVEAU

Feature Tests (145 tests):
  - LoginTest / RegisterTest : credentials, validation, session, logout
  - DoleanceTest : CRUD, ownership, consultation rules, staff access
  - EventTest / AnnouncementTest / AlertTest : CRUD, role restrictions, validation
  - InterventionTest : CRUD, staff-only, role variants
  - VehicleTest : CRUD, maintenance, role hierarchy
  - ProfileTest : view, update, password change
  - DashboardTest : role-based stats
  - CityPublicTest : public data, filtering, sensitive fields
  - SpaSeoTest : meta tags par ville, fallback UUID inconnu, catch-all SPA  # NOUVEAU
```

### Frontend (28 composants React)
- **UI** : Button, Card, Modal, Alert, Badge, Input
- **Layout** : MainLayout (sidebar responsive), Landing (hero SaaS), NotFound
- **Auth** : Login, Register
- **Dashboard** : Stats adaptees au role
- **Doleances** : Liste, Formulaire, Detail avec timeline
- **Evenements** : Liste grille, Formulaire
- **Annonces** : Liste timeline, Formulaire
- **Alertes** : Liste avec severite, Formulaire
- **Interventions** : Liste table/cards, Formulaire
- **Vehicules** : Liste flotte, Formulaire avec maintenances
- **Profil** : Informations + changement mot de passe
- **Abonnement** : Plan 80EUR/mois avec Stripe
- **Ville** : Page publique SEO avec events/annonces/alertes

Build Vite : `385.43 kB JS (108.15 kB gzip)` + `79.77 kB CSS (13.77 kB gzip)` (`vite build` en 1.27s).

### Discord Logging
Service `DiscordLogger` integre dans **les 11 controllers** :
- Logs d'erreur dans chaque bloc catch
- Logs d'information pour les actions importantes (creation, inscription, annulation d'abonnement, ajout de maintenance, etc.)
- Embeds Discord avec couleurs par severite (`info` bleu, `warning` jaune, `error` rouge), timestamp et contexte
- Fire-and-forget : un echec d'envoi vers Discord n'interrompt jamais la requete utilisateur

### PWA
- Manifest avec icones 192x192 et 512x512, `theme_color` `#2563eb`, `display: standalone`
- Service Worker avec strategie network-first + cache fallback hors-ligne
- Support standalone sur mobile (PWA installable)

### Configuration / installation
```bash
git clone https://github.com/maitrecanard/citypulse.git
cd citypulse
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
php artisan test            # 194 passed
composer run dev            # serveur + queue + logs + vite en parallele
```

Variables d'environnement specifiques :
- `DISCORD_WEBHOOK_URL` : webhook Discord (facultatif, logs locaux sinon)
- `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_PRICE_CITY_MONTHLY` : Stripe / Cashier
- `CASHIER_CURRENCY=eur`, `CASHIER_CURRENCY_LOCALE=fr`
- `SANCTUM_STATEFUL_DOMAINS` : domaines autorises pour le cookie de session
