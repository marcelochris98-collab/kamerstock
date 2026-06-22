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
            if (!Schema::hasColumn('settings', 'business_type')) {
                $table->string('business_type')->nullable()->after('invoice_prefix');
            }
            if (!Schema::hasColumn('settings', 'business_type_custom')) {
                $table->string('business_type_custom')->nullable()->after('business_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'business_type')) {
                $table->dropColumn('business_type');
            }
            if (Schema::hasColumn('settings', 'business_type_custom')) {
                $table->dropColumn('business_type_custom');
            }
        });
    }
};
