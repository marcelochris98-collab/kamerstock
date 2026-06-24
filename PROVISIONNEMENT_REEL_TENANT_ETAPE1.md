# Provisionnement réel tenant — Étape 1

## Objectif
Préparer et activer de manière contrôlée la création réelle d’une base de données pour une boutique test.

## Fichiers créés

- app/Console/Commands/ProvisionTenantCommand.php
- PROVISIONNEMENT_REEL_TENANT_ETAPE1.md

## Fichiers modifiés

- app/Services/Platform/TenantProvisioningService.php
- app/Http/Controllers/Landlord/TenantController.php
- routes/web.php
- resources/views/landlord/tenants/show.blade.php
- config/platform.php si applicable
- .env.saas.example si applicable

## Variables .env

- PLATFORM_ENABLE_DB_PROVISIONING=false
- PLATFORM_ALLOW_LOCAL_DB_PROVISIONING=true
- PLATFORM_TENANT_DB_PREFIX=kamerstock_tenant_

## Fonctionnement avec PLATFORM_ENABLE_DB_PROVISIONING=false
Le système ne crée pas réellement la base et conserve le tenant en statut prepared.

## Fonctionnement avec PLATFORM_ENABLE_DB_PROVISIONING=true
Le système peut créer la base de données avec CREATE DATABASE IF NOT EXISTS.

## Sécurité

- aucun mot de passe n’est affiché
- database_password est masqué
- owner_password_plain est masqué
- la boutique legacy_current_db est protégée

## Tests effectués

- php artisan test
- php artisan route:list | findstr provision
- php artisan list | findstr provision
- npm run build

## Limites restantes

- les migrations tenant ne sont pas encore lancées dans cette étape
- le compte propriétaire tenant n’est pas encore créé dans la base tenant
- le routage sous-domaine n’est pas encore activé

## Prochaine étape
Étape 2 : migrations tenant + création du compte propriétaire.
