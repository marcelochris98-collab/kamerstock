<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price_sell_company', 10, 2)->nullable()->after('price_sell');
            $table->decimal('price_sell_reseller', 10, 2)->nullable()->after('price_sell_company');
            $table->decimal('price_sell_wholesale', 10, 2)->nullable()->after('price_sell_reseller');
        });

        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE clients MODIFY COLUMN type ENUM('particulier', 'entreprise', 'revendeur', 'grossiste') DEFAULT 'particulier'");
        } else {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('type')->default('particulier')->change();
            });
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->integer('loyalty_points')->default(0)->after('loyalty_status');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_sell_company', 'price_sell_reseller', 'price_sell_wholesale']);
        });

        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE clients MODIFY COLUMN type ENUM('particulier', 'entreprise', 'revendeur') DEFAULT 'particulier'");
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('loyalty_points');
        });
    }
};
