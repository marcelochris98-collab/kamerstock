<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_histories', function (Blueprint $table) {

            $table->id();

            $table->foreignId('credit_sale_id')
                ->constrained('credit_sales')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action');

            $table->string('title');

            $table->text('description')->nullable();

            $table->decimal('amount', 12, 2)->default(0);

            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_histories');
    }
};
