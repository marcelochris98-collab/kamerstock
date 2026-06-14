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
        // 1. Modifier la table notifications pour ajouter client_id
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('user_id')->constrained('clients')->cascadeOnDelete();
        });

        // 2. Ajouter les paramètres à la table users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('sounds_enabled')->default(true);
            $table->integer('sound_volume')->default(50);
            $table->json('notification_categories')->nullable();
        });

        // 3. Ajouter les paramètres à la table clients
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('sounds_enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'client_id')) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notifications_enabled', 'sounds_enabled', 'sound_volume', 'notification_categories']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['notifications_enabled', 'sounds_enabled']);
        });
    }
};
