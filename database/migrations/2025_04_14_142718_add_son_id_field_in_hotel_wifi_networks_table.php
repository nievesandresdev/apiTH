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
        Schema::table('hotel_wifi_networks', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_wifi_networks', 'son_id')) {
                $table->unsignedBigInteger('son_id')->nullable()->constrained('hotel_wifi_networks')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_wifi_networks', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_wifi_networks', 'son_id')) {
                $table->dropForeign(['son_id']);
                $table->dropColumn('son_id');
            }
        });
    }
};
