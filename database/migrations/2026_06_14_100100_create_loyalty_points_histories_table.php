<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_points_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->integer('points'); // positive for earned, negative for spent
            $table->string('description');
            $table->timestamps();

            $table->index(['client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_points_histories');
    }
};
