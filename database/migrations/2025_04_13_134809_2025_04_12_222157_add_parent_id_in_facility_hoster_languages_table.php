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
        Schema::table('facility_hoster_languages', function (Blueprint $table) {
            if (!Schema::hasColumn('facility_hoster_languages', 'son_id')) {
                $table->unsignedBigInteger('son_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_hoster_languages', function (Blueprint $table) {
            if (Schema::hasColumn('facility_hoster_languages', 'son_id')) {
                $table->dropColumn('son_id');
            }
        });
    }
};
