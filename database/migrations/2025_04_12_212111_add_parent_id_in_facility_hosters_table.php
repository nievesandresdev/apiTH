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
            if (!Schema::hasColumn('facility_hosters', 'son_id')) {
                $table->integer('son_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_hosters', function (Blueprint $table) {
            if (Schema::hasColumn('facility_hosters', 'son_id')) {
                $table->dropColumn('son_id');
            }
        });
    }
};
