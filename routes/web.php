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
use App\Http\Controllers\ClientPortalController;

// Redirection racine
Route::get('/', fn () => redirect()->route('login'));

// Route test temporaire
Route::get('/test-purchases', function () {
    return 'ROUTE PURCHASE OK';
});

// Authentification
require __DIR__ . '/auth.php';

// Routes protégées
Route::middleware(['checkauth'])->group(function () {

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
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

            Route::get('backups', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backups.index');
            Route::post('backups', [App\Http\Controllers\Admin\BackupController::class, 'create'])->name('backups.create');
            Route::get('backups/{filename}/download', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');
            Route::delete('backups/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('backups.destroy');
            Route::post('backups/{filename}/restore', [App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backups.restore');
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
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });

    Route::middleware(['permission:purchases.manage'])->group(function () {
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::post('/purchases/{purchase}/payment', [PurchaseController::class, 'payment'])->name('purchases.payment');
    });

    // Achats Avancés (Advanced Purchases)
    Route::middleware(['permission:purchases.view'])->group(function () {
        Route::get('/advanced-purchases/orders', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersIndex'])->name('advanced_purchases.orders.index');
        Route::get('/advanced-purchases/orders/{order}', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersShow'])->name('advanced_purchases.orders.show');
        Route::get('/advanced-purchases/returns', [App\Http\Controllers\AdvancedPurchaseController::class, 'returnsIndex'])->name('advanced_purchases.returns.index');
        Route::get('/advanced-purchases/returns/{return}', [App\Http\Controllers\AdvancedPurchaseController::class, 'returnsShow'])->name('advanced_purchases.returns.show');
    });

    Route::middleware(['permission:purchases.manage'])->group(function () {
        Route::get('/advanced-purchases/suggestions', [App\Http\Controllers\AdvancedPurchaseController::class, 'suggestions'])->name('advanced_purchases.suggestions');
        Route::get('/advanced-purchases/orders/create', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersCreate'])->name('advanced_purchases.orders.create');
        Route::post('/advanced-purchases/orders', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersStore'])->name('advanced_purchases.orders.store');
        Route::post('/advanced-purchases/orders/{order}/receive', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersReceive'])->name('advanced_purchases.orders.receive');
        Route::post('/advanced-purchases/orders/{order}/cancel', [App\Http\Controllers\AdvancedPurchaseController::class, 'ordersCancel'])->name('advanced_purchases.orders.cancel');
        Route::post('/advanced-purchases/orders/{order}/convert', [App\Http\Controllers\AdvancedPurchaseController::class, 'convertToInvoice'])->name('advanced_purchases.orders.convert');
        Route::get('/advanced-purchases/returns/create', [App\Http\Controllers\AdvancedPurchaseController::class, 'returnsCreate'])->name('advanced_purchases.returns.create');
        Route::post('/advanced-purchases/returns', [App\Http\Controllers\AdvancedPurchaseController::class, 'returnsStore'])->name('advanced_purchases.returns.store');
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