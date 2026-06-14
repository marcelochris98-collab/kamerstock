<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ── PERMISSIONS PAR MODULE ─────────────────────────────

        $permissions = [
            // Catalogue
            ['name' => 'Voir produits',       'slug' => 'products.view',    'module' => 'Catalogue',     'action' => 'view'],
            ['name' => 'Créer produits',      'slug' => 'products.create',  'module' => 'Catalogue',     'action' => 'create'],
            ['name' => 'Modifier produits',   'slug' => 'products.edit',    'module' => 'Catalogue',     'action' => 'edit'],
            ['name' => 'Supprimer produits',  'slug' => 'products.delete',  'module' => 'Catalogue',     'action' => 'delete'],
            ['name' => 'Voir catégories',     'slug' => 'categories.view',  'module' => 'Catalogue',     'action' => 'view'],
            ['name' => 'Gérer catégories',    'slug' => 'categories.manage','module' => 'Catalogue',     'action' => 'manage'],

            // Stock
            ['name' => 'Voir stock',          'slug' => 'stock.view',       'module' => 'Stock',         'action' => 'view'],
            ['name' => 'Gérer stock',         'slug' => 'stock.manage',     'module' => 'Stock',         'action' => 'manage'],

            // Ventes
            ['name' => 'Voir ventes',         'slug' => 'sales.view',       'module' => 'Ventes',        'action' => 'view'],
            ['name' => 'Créer ventes',        'slug' => 'sales.create',     'module' => 'Ventes',        'action' => 'create'],
            ['name' => 'Annuler ventes',      'slug' => 'sales.cancel',     'module' => 'Ventes',        'action' => 'cancel'],

            // Clients
            ['name' => 'Voir clients',        'slug' => 'clients.view',     'module' => 'Clients',       'action' => 'view'],
            ['name' => 'Gérer clients',       'slug' => 'clients.manage',   'module' => 'Clients',       'action' => 'manage'],

            // Fournisseurs
            ['name' => 'Voir fournisseurs',   'slug' => 'suppliers.view',   'module' => 'Fournisseurs',  'action' => 'view'],
            ['name' => 'Gérer fournisseurs',  'slug' => 'suppliers.manage', 'module' => 'Fournisseurs',  'action' => 'manage'],

            // Rapports
            ['name' => 'Voir rapports',       'slug' => 'reports.view',     'module' => 'Rapports',      'action' => 'view'],
            ['name' => 'Exporter rapports',   'slug' => 'reports.export',   'module' => 'Rapports',      'action' => 'export'],

            // Administration
            ['name' => 'Gérer utilisateurs',  'slug' => 'users.manage',     'module' => 'Administration','action' => 'manage'],
            ['name' => 'Gérer rôles',         'slug' => 'roles.manage',     'module' => 'Administration','action' => 'manage'],
            ['name' => 'Voir journal',        'slug' => 'logs.view',        'module' => 'Administration','action' => 'view'],
            ['name' => 'Gérer paramètres',    'slug' => 'settings.manage',  'module' => 'Administration','action' => 'manage'],
            ['name' => 'Voir dashboard',      'slug' => 'dashboard.view',   'module' => 'Administration','action' => 'view'],

            // Nouveaux Modules
            ['name' => 'Accéder messagerie CRM', 'slug' => 'crm.messages',  'module' => 'Clients',       'action' => 'messages'],
            ['name' => 'Voir achats',         'slug' => 'purchases.view',   'module' => 'Achats',        'action' => 'view'],
            ['name' => 'Gérer achats',        'slug' => 'purchases.manage', 'module' => 'Achats',        'action' => 'manage'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // ── RÔLE ADMIN (toutes les permissions) ───────────────

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name'        => 'Administrateur',
                'description' => 'Accès complet au système',
                'is_active'   => true,
            ]
        );

        $admin->permissions()->sync(Permission::pluck('id'));

        // ── RÔLES DE BASE AVEC LEURS PERMISSIONS PAR DÉFAUT ──

        $caissier = Role::firstOrCreate(
            ['slug' => 'caissier'],
            ['name' => 'Caissier', 'description' => 'Gestion des ventes et clients', 'is_active' => true]
        );
        $caissier->permissions()->sync(
            Permission::whereIn('slug', [
                'sales.create',
                'sales.view',
                'clients.view',
                'clients.manage',
                'products.view',
                'dashboard.view'
            ])->pluck('id')
        );

        $magasinier = Role::firstOrCreate(
            ['slug' => 'magasinier'],
            ['name' => 'Magasinier', 'description' => 'Gestion du stock et fournisseurs', 'is_active' => true]
        );
        $magasinier->permissions()->sync(
            Permission::whereIn('slug', [
                'products.view',
                'products.create',
                'products.edit',
                'categories.view',
                'categories.manage',
                'stock.view',
                'stock.manage',
                'suppliers.view',
                'suppliers.manage',
                'purchases.view',
                'purchases.manage',
                'dashboard.view'
            ])->pluck('id')
        );

        $comptable = Role::firstOrCreate(
            ['slug' => 'comptable'],
            ['name' => 'Comptable', 'description' => 'Lecture ventes et rapports', 'is_active' => true]
        );
        $comptable->permissions()->sync(
            Permission::whereIn('slug', [
                'sales.view',
                'reports.view',
                'reports.export',
                'clients.view',
                'suppliers.view',
                'purchases.view',
                'dashboard.view'
            ])->pluck('id')
        );

        $this->command->info('✅ Permissions et rôles créés et synchronisés avec succès !');
    }
}