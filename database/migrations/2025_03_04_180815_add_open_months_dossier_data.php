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
        Schema::table('dossier_data', function (Blueprint $table) {
            if (!Schema::hasColumn('dossier_data', 'openMonths')) {
                $table->integer('openMonths')->after('occupancyRateIncrease')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossier_data', function (Blueprint $table) {
            $table->dropColumn('openMonths');
        });
    }
};
