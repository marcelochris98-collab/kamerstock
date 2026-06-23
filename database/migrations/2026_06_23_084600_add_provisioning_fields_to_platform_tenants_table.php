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
        Schema::connection('landlord')->table('platform_tenants', function (Blueprint $table) {
            if (!Schema::connection('landlord')->hasColumn('platform_tenants', 'provisioning_status')) {
                $table->string('provisioning_status')->nullable()->default('prepared')->after('status');
            }
            if (!Schema::connection('landlord')->hasColumn('platform_tenants', 'provisioning_error')) {
                $table->text('provisioning_error')->nullable()->after('provisioning_status');
            }
            if (!Schema::connection('landlord')->hasColumn('platform_tenants', 'owner_password_plain')) {
                $table->text('owner_password_plain')->nullable()->after('owner_phone');
            }
            if (!Schema::connection('landlord')->hasColumn('platform_tenants', 'owner_login_email')) {
                $table->string('owner_login_email')->nullable()->after('owner_email');
            }
            if (!Schema::connection('landlord')->hasColumn('platform_tenants', 'owner_login_password_generated_at')) {
                $table->timestamp('owner_login_password_generated_at')->nullable()->after('last_login_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->table('platform_tenants', function (Blueprint $table) {
            $table->dropColumn([
                'provisioning_status',
                'provisioning_error',
                'owner_password_plain',
                'owner_login_email',
                'owner_login_password_generated_at',
            ]);
        });
    }
};
