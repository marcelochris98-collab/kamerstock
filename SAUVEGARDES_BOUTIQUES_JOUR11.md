# Sauvegardes par Boutique - Rapport Jour 11

Ce rapport décrit l'implémentation complète du système de sauvegardes de bases de données par boutique, contrôlé et centralisé depuis la console d'administration Landlord de KamerStock.

> [!IMPORTANT]
> Les sauvegardes par boutique sont préparées et contrôlées depuis Landlord. La restauration automatique n’est pas encore activée.

---

## Objectif du Jour 11

Permettre au Super Admin Landlord de voir, lancer, suivre, historiser et nettoyer les sauvegardes des bases de données de chaque boutique cliente de manière isolée et sécurisée.

---

## Architecture de l'Implémentation

### Fichiers Créés
1. **Migration** : [2026_06_23_120000_add_extra_fields_to_platform_tenant_backups_table.php](file:///d:/dev-2026/laragon/www/kamerstock/database/migrations/2026_06_23_120000_add_extra_fields_to_platform_tenant_backups_table.php)
2. **Service** : [TenantBackupService.php](file:///d:/dev-2026/laragon/www/kamerstock/app/Services/Platform/TenantBackupService.php)
3. **Commandes Artisan** :
   * [BackupTenantCommand.php](file:///d:/dev-2026/laragon/www/kamerstock/app/Console/Commands/BackupTenantCommand.php) (`platform:backup-tenant`)
   * [BackupAllTenantsCommand.php](file:///d:/dev-2026/laragon/www/kamerstock/app/Console/Commands/BackupAllTenantsCommand.php) (`platform:backup-all-tenants`)
   * [CleanupBackupsCommand.php](file:///d:/dev-2026/laragon/www/kamerstock/app/Console/Commands/CleanupBackupsCommand.php) (`platform:cleanup-backups`)
4. **Vues** :
   * [show.blade.php](file:///d:/dev-2026/laragon/www/kamerstock/resources/views/landlord/backups/show.blade.php) (Détails d'une sauvegarde)
5. **Suite de Tests** :
   * [TenantBackupTest.php](file:///d:/dev-2026/laragon/www/kamerstock/tests/Feature/Tenancy/TenantBackupTest.php)

### Fichiers Modifiés
1. **Configuration** : [platform.php](file:///d:/dev-2026/laragon/www/kamerstock/config/platform.php)
2. **Modèle** : [TenantBackup.php](file:///d:/dev-2026/laragon/www/kamerstock/app/Models/Platform/TenantBackup.php)
3. **Contrôleur Landlord** : [BackupController.php](file:///d:/dev-2026/laragon/www/kamerstock/app/Http/Controllers/Landlord/BackupController.php)
4. **Tableau de Bord Landlord** : [DashboardController.php](file:///d:/dev-2026/laragon/www/kamerstock/app/Http/Controllers/Landlord/DashboardController.php)
5. **Vues Landlord** :
   * [index.blade.php](file:///d:/dev-2026/laragon/www/kamerstock/resources/views/landlord/backups/index.blade.php) (Amélioration de la liste avec filtres et lanceur)
   * [show.blade.php](file:///d:/dev-2026/laragon/www/kamerstock/resources/views/landlord/tenants/show.blade.php) (Section des sauvegardes d'une boutique spécifique)
   * [dashboard.blade.php](file:///d:/dev-2026/laragon/www/kamerstock/resources/views/landlord/dashboard.blade.php) (Ajout des widgets statistiques de sauvegarde)
6. **Routes** : [web.php](file:///d:/dev-2026/laragon/www/kamerstock/routes/web.php)

---

## Routes Ajoutées

| Méthode | URL | Nom de la Route | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/landlord/backups` | `landlord.backups.index` | Liste globale des sauvegardes avec filtres |
| `GET` | `/landlord/backups/{backup}` | `landlord.backups.show` | Vue détaillée d'une sauvegarde (sans secrets) |
| `POST` | `/landlord/tenants/{tenant}/backups` | `landlord.tenants.backups.store` | Lancement manuel d'une sauvegarde pour une boutique |
| `POST` | `/landlord/backups/{backup}/run` | `landlord.backups.run` | Relance d'une sauvegarde en échec |
| `GET` | `/landlord/backups/{backup}/download` | `landlord.backups.download` | Téléchargement du fichier de dump (si activé) |
| `DELETE` | `/landlord/backups/{backup}` | `landlord.backups.destroy` | Suppression d'une sauvegarde et de son fichier |

---

## Fonctionnement des Modes de Sauvegarde

### Mode A : Simulation / Préparation
* Activé lorsque `PLATFORM_BACKUPS_ENABLED=false` dans la configuration.
* Crée l'enregistrement de sauvegarde dans `platform_tenant_backups` avec le statut `completed` (ou `failed` selon les pré-requis).
* Génère une taille fictive (ex: 150 Ko) et ajoute la mention `mode = simulation` dans les métadonnées.
* N'exécute pas de dump de base de données réel pour éviter de saturer les environnements locaux non configurés.

### Mode B : Sauvegarde Réelle
* Activé lorsque `PLATFORM_BACKUPS_ENABLED=true` et `mysqldump` est configuré et disponible sur le système.
* Tente de se connecter à la base de données spécifique de la boutique grâce aux identifiants décryptés dynamiquement.
* Utilise le processus `mysqldump` de manière isolée pour diffuser le flux de sortie directement dans un fichier temporaire unique (`tempnam()`), prévenant ainsi toute saturation de mémoire PHP.
* Déplace le fichier final de dump vers le disque de stockage configuré dans `platform-backups/{tenant-slug}/YYYY-MM-DD_HH-mm-ss_{tenant-slug}.sql`.
* Calcule une empreinte de contrôle (checksum MD5) et enregistre la taille réelle en octets.
* En cas d'erreur de processus, la trace d'erreur est nettoyée de toute information sensible (comme les mots de passe) avant d'être historisée dans le champ `error_message`.

---

## Sécurité des Sauvegardes

1. **Isolation et Masquage** : Le mot de passe de base de données (`database_password`) et le mot de passe propriétaire (`owner_password_plain`) sont masqués par défaut dans la sérialisation des modèles. De plus, they are cleaned before rendering in views or logging to error reports.
2. **Anti-Traversée de répertoire (Path Traversal)** : Le middleware et le contrôleur de téléchargement vérifient que le chemin ciblé commence strictement par le préfixe configuré (`platform-backups/`).
3. **Restauration verrouillée** : Le bouton de restauration est explicitement affiché comme indisponible afin d'éviter toute restauration accidentelle ou destructive à cette étape du développement.

---

## Audit Logs (Journalisation)

Chaque action sur les sauvegardes est journalisée dans la table centrale `platform_landlord_audit_logs` par le biais de `LandlordAuditService` :
* `backup_created` (Création de l'enregistrement)
* `backup_started` (Lancement de la procédure)
* `backup_completed` (Fin d'exécution avec succès)
* `backup_downloaded` (Téléchargement par un admin)
* `backup_deleted` (Suppression de sauvegarde)
* `backup_cleanup` (Nettoyage automatique des anciennes sauvegardes)

---

## Tests Effectués

* Lancement des tests automatisés d'accès support et des **nouveaux tests de sauvegardes** (`tests/Feature/Tenancy/TenantBackupTest.php`) :
  * Création d'enregistrement
  * Mode simulation
  * Blocage d'intervention sur boutique `prepared`
  * Rétention des $N$ dernières sauvegardes (cleanup)
  * Lancement via Artisan
* Tous les tests phpunit ont réussi (`5 tests, 11 assertions`).
* Compilation correcte des assets front-end via `npm.cmd run build`.
* Lancement manuel réussi des commandes artisan :
  * `platform:backup-tenant boutique-actuelle`
  * `platform:backup-all-tenants`
  * `platform:cleanup-backups`

---

## Limites Restantes
* La restauration automatique n’est pas encore disponible.
* La compression gzip est optionnelle et dépend des utilitaires installés sur le système hôte.

---

## Préparation pour le Jour 12
Le système de statistiques de sauvegarde étant en place sur le Dashboard Landlord, nous sommes prêts pour le **Jour 12 : Statistiques Plateforme**, qui viendra centraliser les métriques d'usage des boutiques de manière sécurisée sans violer leur isolation.
