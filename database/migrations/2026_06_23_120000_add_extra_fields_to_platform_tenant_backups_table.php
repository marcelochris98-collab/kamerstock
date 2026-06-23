<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'landlord';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('landlord')->table('platform_tenant_backups', function (Blueprint $table) {
            if (!Schema::connection('landlord')->hasColumn('platform_tenant_backups', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('metadata');
            }
            if (!Schema::connection('landlord')->hasColumn('platform_tenant_backups', 'checksum')) {
                $table->string('checksum')->nullable()->after('created_by');
            }
            if (!Schema::connection('landlord')->hasColumn('platform_tenant_backups', 'database_name')) {
                $table->string('database_name')->nullable()->after('checksum');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->table('platform_tenant_backups', function (Blueprint $table) {
            $columns = [];
            if (Schema::connection('landlord')->hasColumn('platform_tenant_backups', 'created_by')) {
                $columns[] = 'created_by';
            }
            if (Schema::connection('landlord')->hasColumn('platform_tenant_backups', 'checksum')) {
                $columns[] = 'checksum';
            }
            if (Schema::connection('landlord')->hasColumn('platform_tenant_backups', 'database_name')) {
                $columns[] = 'database_name';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
