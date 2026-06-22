<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'setup_completed')) {
                $table->boolean('setup_completed')->default(false)->after('business_type_custom');
            }
            if (!Schema::hasColumn('settings', 'setup_completed_at')) {
                $table->timestamp('setup_completed_at')->nullable()->after('setup_completed');
            }
            if (!Schema::hasColumn('settings', 'enabled_units')) {
                $table->json('enabled_units')->nullable()->after('setup_completed_at');
            }
            if (!Schema::hasColumn('settings', 'setup_step')) {
                $table->string('setup_step')->nullable()->after('enabled_units');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'setup_completed')) {
                $table->dropColumn('setup_completed');
            }
            if (Schema::hasColumn('settings', 'setup_completed_at')) {
                $table->dropColumn('setup_completed_at');
            }
            if (Schema::hasColumn('settings', 'enabled_units')) {
                $table->dropColumn('enabled_units');
            }
            if (Schema::hasColumn('settings', 'setup_step')) {
                $table->dropColumn('setup_step');
            }
        });
    }
};
