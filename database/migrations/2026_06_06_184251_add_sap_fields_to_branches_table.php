<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->string('sap_db', 100)->nullable()->after('name')->index();
            $table->string('sap_bplid', 50)->nullable()->after('sap_db')->index();
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->dropIndex(['sap_db']);
            $table->dropIndex(['sap_bplid']);
            $table->dropColumn(['sap_db', 'sap_bplid']);
        });
    }
};
