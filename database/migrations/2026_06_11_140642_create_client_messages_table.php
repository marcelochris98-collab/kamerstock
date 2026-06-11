<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('type', ['info', 'credit', 'promotion', 'system'])->default('info');
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_messages');
    }
};