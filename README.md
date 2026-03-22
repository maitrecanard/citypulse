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
- Meta tags dynamiques par ville (Open Graph, description, keywords)
- Structure semantique HTML5
- Manifest PWA avec icones
- Service Worker pour le cache et le mode hors-ligne

## Securite
- Connexion securisee avec cookies httponly (Laravel Sanctum stateful)
- Les donnees sensibles (password, remember_token, stripe_id, etc.) ne sont jamais exposees dans les reponses API
- Les IDs de base de donnees ne sont jamais exposes au frontend ; les liens utilisent des UUID
- Un administre ne peut pas modifier une ville, il peut uniquement creer une doleance, la modifier, ou la supprimer si celle-ci n'est pas encore consultee par l'administration
- Un administre peut modifier uniquement une doleance dont il est proprietaire
- Un administre peut uniquement modifier son profil, son mot de passe, ses informations personnelles
- Les agents (maire, secretaire, agent communaux) ont des permissions strictes par role et par ville
- Policies Laravel pour chaque entite avec autorisation granulaire

## Gestion des vehicules
- Gestion des vehicules par equipe (voiture, camion, utilitaire, engin)
- Gestion de l'entretien des vehicules avec historique complet
- Suivi des prochaines maintenances

## Charte graphique
- Design moderne et dynamique avec Tailwind CSS v4
- Gradients, transitions, hover effects
- Interface responsive mobile-first
- Composants UI reutilisables (Button, Card, Modal, Alert, Badge, Input)
- Theme personnalise avec variables CSS (primary, secondary, accent, danger)

## Exigences
- Toutes les icones sont fonctionnelles (SVG inline)
- Logs Discord integres dans tous les controllers (try/catch + actions importantes)
- Tests unitaires complets : **187 tests, 468 assertions, 0 echec**

## Abonnement
Abonnement a tarif unique de 80EUR par mois sans engagement, gere via Stripe/Laravel Cashier.

---

## Rapport d'implementation

### Architecture

```
citypulse/
  app/
    Http/Controllers/Api/    # 11 controllers API
    Models/                  # 10 modeles Eloquent
    Policies/                # 6 policies d'autorisation
    Services/                # DiscordLogger
    Traits/                  # HasUuid
    Http/Middleware/          # EnsureCitySubscribed
  database/
    factories/               # 9 factories
    migrations/              # 10 migrations custom + Sanctum + Cashier
  resources/
    js/
      app.jsx               # Point d'entree React avec routing
      components/            # 28 composants React
      contexts/              # AuthContext
      hooks/                 # useApi
      services/              # API Axios
    css/app.css             # Tailwind v4 avec theme custom
    views/app.blade.php     # Template SPA
  routes/
    api.php                 # 52 routes API
    web.php                 # SPA catch-all
  public/
    manifest.json           # PWA manifest
    sw.js                   # Service Worker
    icons/                  # Icones PWA
  tests/
    Unit/                   # 4 suites de tests unitaires
    Feature/                # 11 suites de tests fonctionnels
```

### Modeles & Migrations
| Modele | Table | Description |
|--------|-------|-------------|
| User | users | Utilisateurs avec roles, UUID, relations ville |
| City | cities | Communes avec abonnement Stripe |
| Doleance | doleances | Doleances citoyens avec statut et reponse admin |
| Event | events | Evenements communaux |
| Announcement | announcements | Annonces officielles |
| Alert | alerts | Alertes de voisinage avec severite |
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

### API Routes (52 routes)
- **Auth** : POST /login, /register, /logout ; GET /user
- **Doleances** : CRUD complet avec filtrage par role
- **Evenements** : CRUD (staff) + lecture (tous)
- **Annonces** : CRUD (staff) + lecture (tous)
- **Alertes** : CRUD (staff) + lecture (tous)
- **Interventions** : CRUD (staff uniquement)
- **Vehicules** : CRUD (maire/secretaire) + maintenances
- **Profil** : GET/PUT profil, PUT mot de passe
- **Abonnement** : GET/POST/DELETE subscription
- **Ville publique** : GET /cities/{uuid}/public
- **Dashboard** : GET /dashboard (stats par role)

### Tests
```
Tests:    187 passed (468 assertions)
Duration: 3.93s

Unit Tests (46 tests):
  - UserTest: UUID, roles, relations, hidden fields, password hash
  - CityTest: UUID, relations, soft deletes, subscription
  - DoleanceTest: UUID, fillable, hidden, casts, relations
  - DiscordLoggerTest: instantiation, HTTP mocking, error handling

Feature Tests (141 tests):
  - LoginTest: credentials, validation, session, logout
  - RegisterTest: registration, validation, role assignment
  - DoleanceTest: CRUD, ownership, consultation rules, staff access
  - EventTest: CRUD, role restrictions, validation
  - AnnouncementTest: CRUD, role restrictions
  - AlertTest: CRUD, role restrictions, validation
  - InterventionTest: CRUD, staff-only, role variants
  - VehicleTest: CRUD, maintenance, role hierarchy
  - ProfileTest: view, update, password change
  - DashboardTest: role-based stats
  - CityPublicTest: public data, filtering, sensitive fields
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

### Discord Logging
Service `DiscordLogger` integre dans tous les controllers :
- Logs d'erreur dans chaque bloc catch
- Logs d'information pour les actions importantes (creation, inscription, annulation)
- Embeds Discord avec couleurs par severite, timestamp, et contexte

### PWA
- Manifest avec icones 192x192 et 512x512
- Service Worker avec strategie network-first + cache fallback
- Support standalone sur mobile
