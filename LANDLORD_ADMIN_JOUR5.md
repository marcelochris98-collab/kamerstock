# Rapport du Jour 5 : Espace Super Admin / Landlord Séparé

Ce rapport documente la mise en place d'un espace d'administration Super Admin autonome et séparé pour KamerStock, garantissant la confidentialité des données des boutiques et la séparation des responsabilités.

---

## 🎯 Objectif du Jour 5
Créer les fondations logiques et visuelles d'un espace d'administration "Landlord / Super Admin" distinct de l'espace d'administration des boutiques, avec son propre portail d'authentification, sa propre navigation et ses propres interfaces de gestion d'infrastructure, de facturation et de support.

> [!NOTE]
> Le multi-tenant dynamique n’est pas encore activé. L’espace Landlord permet seulement de gérer les métadonnées de la plateforme à ce stade (sans base de données séparée par boutique).

---

## 🔑 Authentification Landlord & Guard
- **Guard `landlord`** ajouté dans `config/auth.php` (driver `session`, provider `platform_landlord_users`).
- **Provider `platform_landlord_users`** déclaré ciblant le modèle Éloquent `App\Models\Platform\LandlordUser`.
- **Modèle `LandlordUser`** étendant `Authenticatable` avec cryptage automatique de mot de passe (`password => hashed`).
- **Redirection des invités** configurée dynamiquement dans `bootstrap/app.php` : les utilisateurs non authentifiés tentant d'accéder à `/landlord/*` ou `/landlord` sont automatiquement redirigés vers `/landlord/login` (et non plus vers `/login` des boutiques).

---

## 📅 Données initiales (Seeders)
- **`PlatformLandlordUserSeeder`** : Crée l'administrateur par défaut dans la table `platform_landlord_users` :
  - **Identifiant** : `admin@kamerstock.cm`
  - **Mot de passe** : `Admin@2026`
  - Prise en charge des variables d'environnement (`LANDLORD_ADMIN_NAME`, `LANDLORD_ADMIN_EMAIL`, `LANDLORD_ADMIN_PASSWORD`).

---

## 🛣️ Routes Landlord Créées (`routes/web.php`)
Toutes les routes de la plateforme sont préfixées par `/landlord` et portent le nom `landlord.*` :

### Routes Publiques (Invitées)
- `GET /landlord/login` (`landlord.login`) : Formulaire de connexion.
- `POST /landlord/login` (`landlord.login.store`) : Soumission des identifiants.

### Routes Protégées (`auth:landlord`)
- `POST /landlord/logout` (`landlord.logout`) : Déconnexion.
- `GET /landlord/dashboard` (`landlord.dashboard`) : Tableau de bord plateforme.
- **Gestion des Boutiques (`landlord.tenants.*`)** :
  - `GET /landlord/tenants` (liste)
  - `GET /landlord/tenants/create` (formulaire création)
  - `POST /landlord/tenants` (enregistrement)
  - `GET /landlord/tenants/{tenant}` (détails & historique)
  - `GET /landlord/tenants/{tenant}/edit` (formulaire édition)
  - `PUT /landlord/tenants/{tenant}` (mise à jour)
  - `POST /landlord/tenants/{tenant}/suspend` (action suspension)
  - `POST /landlord/tenants/{tenant}/activate` (action activation)
  - `POST /landlord/tenants/{tenant}/read-only` (action passage en lecture seule)
- **Gestion des Plans (`landlord.plans.*`)** :
  - `GET /landlord/plans` (liste)
  - `GET /landlord/plans/create` (création)
  - `POST /landlord/plans` (enregistrement)
  - `GET /landlord/plans/{plan}/edit` (édition)
  - `PUT /landlord/plans/{plan}` (mise à jour)
- **Visualisations** :
  - `GET /landlord/subscriptions` : Liste des abonnements souscrits par boutique.
  - `GET /landlord/payments` : Liste des transactions de paiement enregistrées.
  - `GET /landlord/support-accesses` : Suivi des demandes d'accès support.
  - `GET /landlord/backups` : Historique des sauvegardes de bases de données.
  - `GET /landlord/audit-logs` : Actions super admin enregistrées (connexions, modifications, suspensions).

---

## 🏗️ Contrôleurs & Vues Créés (Sous `app/Http/Controllers/Landlord/` et `resources/views/landlord/`)

### Contrôleurs
- `AuthController.php` : Gère le formulaire de connexion, la tentative d'accès via le guard `landlord` (avec contrôle `is_active` et mise à jour `last_login_at`), et la déconnexion.
- `DashboardController.php` : Agrège les compteurs de plateformes sans données boutiques.
- `TenantController.php` : Gère le CRUD des boutiques et les mutations d'états d'accès.
- `PlanController.php` : Gère les plans d'abonnements et la sérialisation des listes de fonctionnalités.
- `SubscriptionController`, `PaymentController`, `SupportAccessController`, `BackupController`, `AuditLogController` : Gèrent l'affichage des listes de consultation correspondantes.

### Vues & Layouts
- **Layout `layouts/landlord.blade.php`** : Sidebar, en-tête et conteneur distinct (thème indigo/ardoise moderne).
- **Vues** : Écriture de toutes les interfaces de CRUD et de listes.
- **Confidentialité** : Les données métier des boutiques (ventes, produits, clients, fournisseurs) ne sont **pas chargées** dans l'espace Landlord. Un bandeau d'avertissement rappelle cette règle sur le tableau de bord.

---

## 🛡️ Séparation Boutique / Landlord
- Le lien temporaire "Plateforme SaaS" a été **retiré** de la sidebar de l'espace boutique.
- L'espace boutique (`/admin`) et le login boutique (`/login`) restent entièrement isolés de l'espace plateforme (`/landlord`).
- Les sessions des guards `web` et `landlord` sont indépendantes.
- L'ancienne route `/admin/platform/overview` a été transformée en redirection automatique vers le tableau de bord landlord pour les utilisateurs connectés.

---

## 🚧 Limites actuelles & Prochaines étapes (Jour 6)
- **Limites** : Les bases de données des boutiques ne sont pas encore séparées. La création d'une boutique n'exécute pas encore automatiquement de migration spécifique ou de création d'utilisateur boutique.
- **Jour 6** : Implémentation du multi-tenant dynamique par base de données (sélection à chaud de la base en fonction du tenant actif) et automatisation de l'initialisation lors de la création d'une boutique.
