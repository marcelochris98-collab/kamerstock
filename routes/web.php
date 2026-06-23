<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CreditSettingController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\ClientPortalController;

// Redirection racine ou landing page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Route test temporaire
Route::get('/test-purchases', function () {
    return 'ROUTE PURCHASE OK';
});

// Authentification
require __DIR__ . '/auth.php';

// Routes protégées
Route::middleware(['checkauth', 'audit'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Zone administration
    Route::prefix('admin')->name('admin.')->group(function () {

        // Utilisateurs
        Route::middleware(['permission:users.manage'])->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        });

        // Rôles & Permissions
        Route::middleware(['permission:roles.manage'])->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
            Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.permissions');
        });

        // Paramètres entreprise & Sauvegardes
        Route::middleware(['permission:settings.manage'])->group(function () {
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::match(['put', 'post'], 'settings', [SettingController::class, 'update'])->name('settings.update');
            Route::get('settings/default-categories', [SettingController::class, 'showDefaultCategories'])->name('settings.default-categories');
            Route::post('settings/default-categories', [SettingController::class, 'storeDefaultCategories'])->name('settings.default-categories.store');

            Route::post('settings/finish', [SettingController::class, 'finish'])->name('settings.finish');
            Route::post('settings/reset', [SettingController::class, 'reset'])->name('settings.reset');

            Route::get('backups', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backups.index');
            Route::post('backups', [App\Http\Controllers\Admin\BackupController::class, 'create'])->name('backups.create');
            Route::get('backups/{filename}/download', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');
            Route::delete('backups/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('backups.destroy');
            Route::post('backups/{filename}/restore', [App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backups.restore');

            Route::get('platform/overview', function () {
                return redirect()->route('landlord.dashboard');
            })->name('platform.overview');
        });

        Route::middleware(['permission:logs.view'])->group(function () {
            Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        });
    });

    // Routes AJAX intelligentes
    Route::get('/categories/lookup', [CategoryController::class, 'lookup'])->name('categories.lookup');
    Route::get('/suppliers/lookup', [SupplierController::class, 'lookup'])->name('suppliers.lookup');
    Route::get('/clients/lookup', [ClientController::class, 'lookup'])->name('clients.lookup');
    Route::get('/global-search', [App\Http\Controllers\GlobalSearchController::class, 'search'])->name('global_search');

    // Catalogue / Produits
    Route::middleware(['permission:products.create'])->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
    });

    Route::middleware(['permission:products.view'])->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    });

    Route::middleware(['permission:products.edit'])->group(function () {
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
    });

    Route::middleware(['permission:products.delete'])->group(function () {
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Catégories
    Route::middleware(['permission:categories.manage'])->group(function () {
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Stock
    Route::middleware(['permission:stock.view'])->group(function () {
        Route::get('stock', [StockMovementController::class, 'index'])->name('stock.index');
        Route::get('stock/history', [StockMovementController::class, 'history'])->name('stock.history');
    });

    Route::middleware(['permission:stock.manage'])->group(function () {
        Route::post('stock', [StockMovementController::class, 'store'])->name('stock.store');
    });

  // Fournisseurs
Route::middleware(['permission:suppliers.view'])->group(function () {
    Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
});

Route::middleware(['permission:suppliers.manage'])->group(function () {
    Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
});

    // Clients
    Route::middleware(['permission:clients.view'])->group(function () {
        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    });

    Route::middleware(['permission:clients.manage'])->group(function () {
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    });

    // Ventes
    Route::middleware(['permission:sales.view'])->group(function () {
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    });

    Route::middleware(['permission:sales.create'])->group(function () {
        Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
    });

    Route::middleware(['permission:sales.cancel'])->group(function () {
        Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
    });

    // Devis & Proformas
    Route::middleware(['permission:sales.create'])->group(function () {
        Route::get('quotes/create', [App\Http\Controllers\QuoteController::class, 'create'])->name('quotes.create');
        Route::post('quotes', [App\Http\Controllers\QuoteController::class, 'store'])->name('quotes.store');
        Route::post('quotes/{quote}/convert', [App\Http\Controllers\QuoteController::class, 'convertToSale'])->name('quotes.convert');
        Route::delete('quotes/{quote}', [App\Http\Controllers\QuoteController::class, 'destroy'])->name('quotes.destroy');
    });

    Route::middleware(['permission:sales.view'])->group(function () {
        Route::get('quotes', [App\Http\Controllers\QuoteController::class, 'index'])->name('quotes.index');
        Route::get('quotes/{quote}', [App\Http\Controllers\QuoteController::class, 'show'])->name('quotes.show');
        Route::get('quotes/{quote}/print', [App\Http\Controllers\QuoteController::class, 'print'])->name('quotes.print');
    });

    // Crédits clients
    Route::middleware(['permission:clients.view'])->group(function () {
        Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');
        Route::get('/credits-payments-history', [CreditController::class, 'paymentsHistory'])->name('credits.payments.history');
        Route::get('/credits/{credit}', [CreditController::class, 'show'])->name('credits.show');
        Route::post('/credits/{credit}/payment', [CreditController::class, 'payment'])->name('credits.payment');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Paramètres crédit intelligent
    Route::middleware(['permission:settings.manage'])->group(function () {
        Route::get('/settings/credit', [CreditSettingController::class, 'edit'])->name('settings.credit.edit');
        Route::put('/settings/credit', [CreditSettingController::class, 'update'])->name('settings.credit.update');
    });

    // Achats & Fournisseurs (Standard Purchases)
    Route::middleware(['permission:purchases.view'])->group(function () {
        Route::get('/purchases/dashboard', [PurchaseController::class, 'dashboard'])->name('purchases.dashboard');
        Route::get('/purchases/debts', [PurchaseController::class, 'debts'])->name('purchases.debts');
        Route::get('/purchases/payments-history', [PurchaseController::class, 'paymentsHistory'])->name('purchases.payments-history');
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    });

    Route::middleware(['permission:purchases.manage'])->group(function () {
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::post('/purchases/{purchase}/payment', [PurchaseController::class, 'payment'])->name('purchases.payment');
    });

    Route::middleware(['permission:purchases.view'])->group(function () {
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });

    // Achats Avancés (Advanced Purchases)
    Route::middleware(['permission:purchases.view'])->group(function () {
        Route::get('/advanced-purchases/orders', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersIndex'])->name('advanced_purchases.orders.index');
        Route::get('/advanced-purchases/returns', [App\Http\Controllers\AdvancedPurchaseController::class, 'returnsIndex'])->name('advanced_purchases.returns.index');
    });

    Route::middleware(['permission:purchases.manage'])->group(function () {
        Route::get('/advanced-purchases/suggestions', [App\Http\Controllers\AdvancedPurchaseController::class, 'suggestions'])->name('advanced_purchases.suggestions');
        Route::get('/advanced-purchases/orders/create', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersCreate'])->name('advanced_purchases.orders.create');
        Route::post('/advanced-purchases/orders', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersStore'])->name('advanced_purchases.orders.store');
        Route::post('/advanced-purchases/orders/{order}/receive', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersReceive'])->name('advanced_purchases.orders.receive');
        Route::post('/advanced-purchases/orders/{order}/convert', [App\Http\Controllers\AdvancedPurchaseController::class, 'convertToInvoice'])->name('advanced_purchases.orders.convert');
        Route::post('/advanced-purchases/orders/{order}/cancel', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersCancel'])->name('advanced_purchases.orders.cancel');
        Route::get('/advanced-purchases/returns/create', [App\Http\Controllers\AdvancedPurchaseController::class, 'returnsCreate'])->name('advanced_purchases.returns.create');
        Route::post('/advanced-purchases/returns', [App\Http\Controllers\AdvancedPurchaseController::class, 'returnsStore'])->name('advanced_purchases.returns.store');
    });

    Route::middleware(['permission:purchases.view'])->group(function () {
        Route::get('/advanced-purchases/orders/{order}', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersShow'])->name('advanced_purchases.orders.show');
        Route::get('/advanced-purchases/returns/{return}', [App\Http\Controllers\AdvancedPurchaseController::class, 'returnsShow'])->name('advanced_purchases.returns.show');
    });

    // Activation portail client depuis l'administration
    Route::post('/clients/{client}/portal/enable', [ClientPortalController::class, 'enable']) ->name('clients.portal.enable');
    Route::post('/clients/{client}/portal/access', [ClientPortalController::class, 'sendAccess']) ->name('clients.portal.access');
    Route::post('/clients/{client}/portal/disable', [ClientPortalController::class, 'disable']) ->name('clients.portal.disable');
    Route::post('/client/messages/send', [ClientPortalController::class, 'sendMessage']) ->name('client.portal.messages.send');

    // CRM Messages Administration
    Route::middleware(['permission:crm.messages'])->group(function () {
        Route::get('/crm-messages', [App\Http\Controllers\Admin\CrmMessageController::class, 'index'])->name('admin.crm_messages.index');
        Route::get('/crm-messages/{client}/history', [App\Http\Controllers\Admin\CrmMessageController::class, 'history'])->name('admin.crm_messages.history');
        Route::post('/crm-messages/{client}/reply', [App\Http\Controllers\Admin\CrmMessageController::class, 'reply'])->name('admin.crm_messages.reply');
    });

    // Rapports & Statistiques (Reports & Stats)
    Route::middleware(['permission:reports.view'])->group(function () {
        Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    });

    // Exports de Données (Exports)
    Route::get('/export/{type}', [App\Http\Controllers\ExportController::class, 'export'])->name('export');

    // Profil utilisateur
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/notifications', [App\Http\Controllers\ProfileController::class, 'updateNotifications'])->name('profile.notifications.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Portail client public
Route::get('/client/login', [ClientPortalController::class, 'login'])->name('client.portal.login');
Route::post('/client/login', [ClientPortalController::class, 'authenticate'])->name('client.portal.authenticate');
Route::post('/client/logout', [ClientPortalController::class, 'logout'])->name('client.portal.logout');

Route::get('/client/dashboard', [ClientPortalController::class, 'dashboard'])->name('client.portal.dashboard');
Route::get('/client/sales', [ClientPortalController::class, 'sales'])->name('client.portal.sales');
Route::get('/client/credits', [ClientPortalController::class, 'credits'])->name('client.portal.credits');
Route::get('/client/messages', [ClientPortalController::class, 'messages'])->name('client.portal.messages');
Route::get('/client/messages/json', [ClientPortalController::class, 'getMessagesJson'])->name('client.portal.messages.json');
Route::post('/client/messages/send-ajax', [ClientPortalController::class, 'sendMessageAjax'])->name('client.portal.messages.send_ajax');
Route::post('/client/settings/notifications', [ClientPortalController::class, 'updateNotificationSettings'])->name('client.portal.settings.notifications');
Route::get('/client/notifications/poll', [ClientPortalController::class, 'pollNotifications'])->name('client.portal.notifications.poll');

// =========================================================================
// Landlord / Super Admin Area
// =========================================================================
Route::prefix('landlord')->name('landlord.')->group(function () {
    
    // Public routes (Guest landlord)
    Route::middleware('guest:landlord')->group(function () {
        Route::get('login', [\App\Http\Controllers\Landlord\AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [\App\Http\Controllers\Landlord\AuthController::class, 'login'])->name('login.store');
    });

    // Protected routes (Auth landlord)
    Route::middleware('auth:landlord')->group(function () {
        Route::post('logout', [\App\Http\Controllers\Landlord\AuthController::class, 'logout'])->name('logout');
        
        Route::get('dashboard', [\App\Http\Controllers\Landlord\DashboardController::class, 'index'])->name('dashboard');
        
        // Tenants CRUD & Actions
        Route::get('tenants', [\App\Http\Controllers\Landlord\TenantController::class, 'index'])->name('tenants.index');
        Route::get('tenants/create', [\App\Http\Controllers\Landlord\TenantController::class, 'create'])->name('tenants.create');
        Route::post('tenants', [\App\Http\Controllers\Landlord\TenantController::class, 'store'])->name('tenants.store');
        Route::post('tenants/register-legacy', [\App\Http\Controllers\Landlord\TenantController::class, 'registerLegacy'])->name('tenants.register_legacy');
        Route::get('tenants/{tenant}', [\App\Http\Controllers\Landlord\TenantController::class, 'show'])->name('tenants.show');
        Route::get('tenants/{tenant}/edit', [\App\Http\Controllers\Landlord\TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('tenants/{tenant}', [\App\Http\Controllers\Landlord\TenantController::class, 'update'])->name('tenants.update');
        Route::post('tenants/{tenant}/suspend', [\App\Http\Controllers\Landlord\TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/activate', [\App\Http\Controllers\Landlord\TenantController::class, 'activate'])->name('tenants.activate');
        Route::post('tenants/{tenant}/read-only', [\App\Http\Controllers\Landlord\TenantController::class, 'readOnly'])->name('tenants.read_only');
        Route::post('tenants/{tenant}/regenerate-owner-password', [\App\Http\Controllers\Landlord\TenantController::class, 'regenerateOwnerPassword'])->name('tenants.regenerate_owner_password');

        // Plans CRUD
        Route::get('plans', [\App\Http\Controllers\Landlord\PlanController::class, 'index'])->name('plans.index');
        Route::get('plans/create', [\App\Http\Controllers\Landlord\PlanController::class, 'create'])->name('plans.create');
        Route::post('plans', [\App\Http\Controllers\Landlord\PlanController::class, 'store'])->name('plans.store');
        Route::get('plans/{plan}/edit', [\App\Http\Controllers\Landlord\PlanController::class, 'edit'])->name('plans.edit');
        Route::put('plans/{plan}', [\App\Http\Controllers\Landlord\PlanController::class, 'update'])->name('plans.update');

        // Consultations
        Route::get('subscriptions', [\App\Http\Controllers\Landlord\SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('payments', [\App\Http\Controllers\Landlord\PaymentController::class, 'index'])->name('payments.index');
        // Support Accesses
        Route::get('support-accesses', [\App\Http\Controllers\Landlord\SupportAccessController::class, 'index'])->name('support.index');
        Route::get('support-accesses/create', [\App\Http\Controllers\Landlord\SupportAccessController::class, 'create'])->name('support.create');
        Route::post('support-accesses', [\App\Http\Controllers\Landlord\SupportAccessController::class, 'store'])->name('support.store');
        Route::get('support-accesses/{supportAccess}', [\App\Http\Controllers\Landlord\SupportAccessController::class, 'show'])->name('support.show');
        Route::post('support-accesses/{supportAccess}/activate', [\App\Http\Controllers\Landlord\SupportAccessController::class, 'activate'])->name('support.activate');
        Route::post('support-accesses/{supportAccess}/revoke', [\App\Http\Controllers\Landlord\SupportAccessController::class, 'revoke'])->name('support.revoke');
        Route::post('support-accesses/expire-old', [\App\Http\Controllers\Landlord\SupportAccessController::class, 'expireOld'])->name('support.expire_old');
        Route::get('support-accesses/{supportAccess}/enter', [\App\Http\Controllers\Landlord\SupportAccessController::class, 'enter'])->name('support.enter');
        
        // Tenant specific support creation
        Route::get('tenants/{tenant}/support', [\App\Http\Controllers\Landlord\SupportAccessController::class, 'tenantSupport'])->name('tenants.support');
        Route::post('tenants/{tenant}/support', [\App\Http\Controllers\Landlord\SupportAccessController::class, 'tenantSupportStore'])->name('tenants.support.store');

        Route::get('backups', [\App\Http\Controllers\Landlord\BackupController::class, 'index'])->name('backups.index');
        Route::get('audit-logs', [\App\Http\Controllers\Landlord\AuditLogController::class, 'index'])->name('audit_logs.index');
    });
});

// =========================================================================
// Routes de Résolution Multi-Tenant
// =========================================================================
Route::get('/tenant/pending', function (\Illuminate\Http\Request $request) {
    $tenantSlug = $request->query('tenant');
    $tenant = null;
    if ($tenantSlug) {
        $tenant = \App\Models\Platform\Tenant::on('landlord')->where('slug', $tenantSlug)->first();
    }
    return view('tenant.pending', [
        'tenant' => $tenant,
        'tenant_name' => $tenant ? $tenant->name : 'Boutique'
    ]);
})->name('tenant.pending');

Route::get('/support/exit', function () {
    $tenantId = session('support_tenant_id');
    $tenant = null;
    if ($tenantId) {
        $tenant = \App\Models\Platform\Tenant::on('landlord')->find($tenantId);
    }
    
    // Clear session variables
    session()->forget(['support_access_id', 'support_tenant_id']);
    app(\App\Services\Platform\SupportContext::class)->clear();

    if ($tenant) {
        \App\Services\Platform\LandlordAuditService::record(
            'support_access_exited',
            $tenant,
            "L'administrateur Landlord a quitté la session de support sur la boutique : {$tenant->name}"
        );
    }

    if (auth('landlord')->check()) {
        return redirect()->route('landlord.support.index')
            ->with('success', "Session de support fermée avec succès.");
    }

    return redirect()->route('dashboard');
})->name('support.exit');

Route::get('/tenant-debug', function () {
    abort_unless(app()->environment('local', 'testing'), 404);

    $context = app(\App\Services\Tenancy\TenantContext::class);
    $tenant = $context->tenant();
    $dbManager = app(\App\Services\Tenancy\TenantDatabaseManager::class);

    return response()->json([
        'tenant_resolved' => $context->hasTenant(),
        'tenant' => $tenant ? [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'status' => $tenant->status,
            'provisioning_status' => $tenant->provisioning_status,
            'database_name' => $tenant->database_name,
            'database_host' => $tenant->database_host,
            'database_port' => $tenant->database_port,
            'domain' => $tenant->domain,
            'subdomain' => $tenant->subdomain,
        ] : null,
        'tenancy_enabled' => config('platform.tenancy_enabled'),
        'tenant_resolution_enabled' => config('platform.tenant_resolution_enabled'),
        'default_connection' => \Illuminate\Support\Facades\DB::getDefaultConnection(),
        'can_use_tenant_database' => $tenant ? $dbManager->canUseTenantDatabase($tenant) : false,
        'connection_tenant_config' => [
            'driver' => config('database.connections.tenant.driver'),
            'host' => config('database.connections.tenant.host'),
            'port' => config('database.connections.tenant.port'),
            'database' => config('database.connections.tenant.database'),
            'username' => config('database.connections.tenant.username'),
        ]
    ]);
})->name('tenant.debug');

