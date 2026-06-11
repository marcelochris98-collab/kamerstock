<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'portal_enabled')) {
                $table->boolean('portal_enabled')->default(false)->after('credit_blocked');
            }

            if (!Schema::hasColumn('clients', 'portal_pin')) {
                $table->string('portal_pin')->nullable()->after('portal_enabled');
            }

            if (!Schema::hasColumn('clients', 'portal_last_login_at')) {
                $table->timestamp('portal_last_login_at')->nullable()->after('portal_pin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'portal_enabled',
                'portal_pin',
                'portal_last_login_at',
            ]);
        });
    }
};