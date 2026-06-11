<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action')->nullable();
            $table->string('title');
            $table->text('description')->nullable();

            $table->decimal('amount', 15, 2)->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['supplier_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_histories');
    }
};
