<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;

// ── Redirection racine ─────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── Authentification (Breeze) ──────────────────────────────
require __DIR__.'/auth.php';

// ── Routes protégées ───────────────────────────────────────
Route::middleware(['checkauth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── ZONE ADMINISTRATION ──────────────────────────────────
    Route::prefix('admin')->name('admin.')->group(function () {

        // Utilisateurs (Protégé par sa propre permission)
        Route::middleware(['permission:users.manage'])->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        });

        // Rôles & Permissions (Protégé par sa propre permission)
        Route::middleware(['permission:users.manage'])->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
            Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.permissions');
        });

        // Paramètres (CORRIGÉ : Changement du POST en PUT pour correspondre au formulaire)
        Route::middleware(['permission:settings.manage'])->group(function () {
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        });
    });

    // ── CATALOGUE ──────────────────────────────────────────
    // 1. Déclarer d'abord la route spécifique "create"
    Route::middleware(['permission:products.create'])->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
    });

    // 2. Déclarer les routes d'affichage de la liste et du détail ensuite
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

    // ── CATEGORIES ─────────────────────────────────────────
    Route::middleware(['permission:categories.manage'])->group(function () {
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // ── STOCK ──────────────────────────────────────────────
    Route::middleware(['permission:stock.view'])->group(function () {
        Route::get('stock', [StockMovementController::class, 'index'])->name('stock.index');
    });

    Route::middleware(['permission:stock.manage'])->group(function () {
        Route::post('stock', [StockMovementController::class, 'store'])->name('stock.store');
    });

    // ── FOURNISSEURS ───────────────────────────────────────
    Route::middleware(['permission:suppliers.view'])->group(function () {
        Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    });

    Route::middleware(['permission:suppliers.manage'])->group(function () {
        Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    // ── CLIENTS ────────────────────────────────────────────
    Route::middleware(['permission:clients.view'])->group(function () {
        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    });

    Route::middleware(['permission:clients.manage'])->group(function () {
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    });

    // ── VENTES ─────────────────────────────────────────────
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

});
