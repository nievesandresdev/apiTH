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
        Schema::table('hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('hotels', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable();
                $table->foreign('city_id')->references('id')->on('cities');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('hotels', 'city_id')) {
                $table->dropColumn('city_id');
            }
        });
    }
};
