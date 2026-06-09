<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_settings', function (Blueprint $table) {
            $table->id();

            $table->integer('min_sales')->default(10);
            $table->integer('min_months')->default(2);
            $table->integer('min_score')->default(40);

            $table->decimal('regular_coefficient', 5, 2)->default(0.50);
            $table->decimal('loyal_coefficient', 5, 2)->default(0.75);
            $table->decimal('premium_coefficient', 5, 2)->default(1.00);

            $table->boolean('allow_regular')->default(true);
            $table->boolean('allow_loyal')->default(true);
            $table->boolean('allow_premium')->default(true);

            $table->boolean('allow_high_risk')->default(false);
            $table->boolean('allow_admin_exception')->default(true);

            $table->decimal('max_credit_limit', 12, 2)->default(2000000);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_settings');
    }
};
