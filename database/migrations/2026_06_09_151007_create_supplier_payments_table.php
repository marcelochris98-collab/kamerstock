<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->foreignId('purchase_id')
                ->nullable()
                ->constrained('purchases')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->decimal('amount', 15, 2)->default(0);

            $table->enum('payment_method', ['cash', 'orange_money', 'mtn_money', 'virement'])->default('cash');

            $table->string('internal_reference')->nullable();
            $table->string('external_reference')->nullable();
            $table->string('reference')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['supplier_id', 'purchase_id']);
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
