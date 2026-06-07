<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedTinyInteger('loyalty_score')->default(0);
            $table->enum('loyalty_status', ['occasionnel', 'regulier', 'fidele', 'premium'])->default('occasionnel');
            $table->enum('risk_level', ['faible', 'moyen', 'eleve'])->default('moyen');

            $table->decimal('recommended_credit_limit', 12, 2)->default(0);
            $table->decimal('credit_used', 12, 2)->default(0);
            $table->decimal('credit_available', 12, 2)->default(0);

            $table->boolean('credit_blocked')->default(false);
            $table->text('credit_block_reason')->nullable();

            $table->timestamp('last_score_calculated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'loyalty_score',
                'loyalty_status',
                'risk_level',
                'recommended_credit_limit',
                'credit_used',
                'credit_available',
                'credit_blocked',
                'credit_block_reason',
                'last_score_calculated_at',
            ]);
        });
    }
};
