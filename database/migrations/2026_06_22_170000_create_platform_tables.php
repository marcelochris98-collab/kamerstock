<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'landlord';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. platform_landlord_users
        if (!Schema::connection('landlord')->hasTable('platform_landlord_users')) {
            Schema::connection('landlord')->create('platform_landlord_users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role')->default('super_admin');
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. platform_tenants
        if (!Schema::connection('landlord')->hasTable('platform_tenants')) {
            Schema::connection('landlord')->create('platform_tenants', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique()->nullable();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('owner_name')->nullable();
                $table->string('owner_email')->nullable();
                $table->string('owner_phone')->nullable();
                $table->string('business_type')->nullable();
                $table->string('business_type_custom')->nullable();
                $table->string('status')->default('trial');
                $table->string('database_name')->nullable();
                $table->string('database_username')->nullable();
                $table->text('database_password')->nullable();
                $table->string('database_host')->nullable();
                $table->string('database_port')->nullable();
                $table->string('domain')->nullable();
                $table->string('subdomain')->nullable();
                $table->string('logo')->nullable();
                $table->string('timezone')->default('Africa/Douala');
                $table->string('currency')->default('FCFA');
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('subscription_ends_at')->nullable();
                $table->timestamp('suspended_at')->nullable();
                $table->timestamp('read_only_at')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->json('settings')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. platform_plans
        if (!Schema::connection('landlord')->hasTable('platform_plans')) {
            Schema::connection('landlord')->create('platform_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->decimal('price_monthly', 12, 2)->default(0);
                $table->decimal('price_yearly', 12, 2)->nullable();
                $table->string('currency')->default('FCFA');
                $table->integer('max_users')->nullable();
                $table->integer('max_products')->nullable();
                $table->integer('max_clients')->nullable();
                $table->integer('max_storage_mb')->nullable();
                $table->integer('max_branches')->nullable();
                $table->json('features')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 4. platform_subscriptions
        if (!Schema::connection('landlord')->hasTable('platform_subscriptions')) {
            Schema::connection('landlord')->create('platform_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
                $table->foreignId('plan_id')->nullable()->constrained('platform_plans')->nullOnDelete();
                $table->string('status')->default('trial');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('currency')->default('FCFA');
                $table->string('billing_cycle')->nullable();
                $table->boolean('auto_renew')->default(false);
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamp('suspended_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 5. platform_subscription_payments
        if (!Schema::connection('landlord')->hasTable('platform_subscription_payments')) {
            Schema::connection('landlord')->create('platform_subscription_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
                $table->foreignId('subscription_id')->nullable()->constrained('platform_subscriptions')->nullOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('currency')->default('FCFA');
                $table->string('payment_method')->nullable();
                $table->string('reference')->nullable();
                $table->string('external_reference')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('period_start')->nullable();
                $table->timestamp('period_end')->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->integer('created_by')->nullable();
                $table->timestamps();
            });
        }

        // 6. platform_tenant_domains
        if (!Schema::connection('landlord')->hasTable('platform_tenant_domains')) {
            Schema::connection('landlord')->create('platform_tenant_domains', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
                $table->string('domain')->unique();
                $table->string('type')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->boolean('is_verified')->default(false);
                $table->timestamp('verified_at')->nullable();
                $table->string('ssl_status')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 7. platform_support_accesses
        if (!Schema::connection('landlord')->hasTable('platform_support_accesses')) {
            Schema::connection('landlord')->create('platform_support_accesses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
                $table->integer('requested_by')->nullable();
                $table->integer('granted_by')->nullable();
                $table->integer('granted_to')->nullable();
                $table->text('reason')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->integer('revoked_by')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // 8. platform_tenant_backups
        if (!Schema::connection('landlord')->hasTable('platform_tenant_backups')) {
            Schema::connection('landlord')->create('platform_tenant_backups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
                $table->string('filename');
                $table->string('path')->nullable();
                $table->string('disk')->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();
                $table->string('status')->default('pending');
                $table->string('backup_type')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamp('downloaded_at')->nullable();
                $table->text('error_message')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // 9. platform_landlord_audit_logs
        if (!Schema::connection('landlord')->hasTable('platform_landlord_audit_logs')) {
            Schema::connection('landlord')->create('platform_landlord_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('landlord_user_id')->nullable();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('action');
                $table->text('description')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->foreign('tenant_id')->references('id')->on('platform_tenants')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('platform_landlord_audit_logs');
        Schema::connection('landlord')->dropIfExists('platform_tenant_backups');
        Schema::connection('landlord')->dropIfExists('platform_support_accesses');
        Schema::connection('landlord')->dropIfExists('platform_tenant_domains');
        Schema::connection('landlord')->dropIfExists('platform_subscription_payments');
        Schema::connection('landlord')->dropIfExists('platform_subscriptions');
        Schema::connection('landlord')->dropIfExists('platform_plans');
        Schema::connection('landlord')->dropIfExists('platform_tenants');
        Schema::connection('landlord')->dropIfExists('platform_landlord_users');
    }
};
