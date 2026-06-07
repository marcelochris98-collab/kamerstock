<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_payments', function (Blueprint $table) {
            $table->string('internal_reference')->nullable()->after('payment_method');
            $table->string('external_reference')->nullable()->after('internal_reference');
        });
    }

    public function down(): void
    {
        Schema::table('credit_payments', function (Blueprint $table) {
            $table->dropColumn([
                'internal_reference',
                'external_reference',
            ]);
        });
    }
};
