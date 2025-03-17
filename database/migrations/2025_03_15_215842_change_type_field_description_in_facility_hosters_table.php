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
        Schema::table('facility_hosters', function (Blueprint $table) {
            if (Schema::hasColumn('facility_hosters', 'description')) {
                $table->text('description')->nullable()->change();
            }
        });
        Schema::table('facility_hoster_languages', function (Blueprint $table) {
            if (Schema::hasColumn('facility_hoster_languages', 'description')) {
                $table->text('description')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_hosters', function (Blueprint $table) {
            //
        });
    }
};
