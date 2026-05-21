<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('reference')->unique();
            $table->enum('unit', ['piece', 'metre', 'kg', 'litre', 'boite', 'sachet'])->default('piece');
            $table->decimal('price_buy', 10, 2)->default(0);
            $table->decimal('price_sell', 10, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->integer('alert_threshold')->default(5);
            $table->decimal('tax_rate', 5, 2)->default(17.50);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};