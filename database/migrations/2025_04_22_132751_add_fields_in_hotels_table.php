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
            if (!Schema::hasColumn('hotels', 'chat_service_enabled')) {
                $table->boolean('chat_service_enabled')->default(true);
            }
            if (!Schema::hasColumn('hotels', 'checkin_service_enabled')) {
                $table->boolean('checkin_service_enabled')->default(true);
            }
            if (!Schema::hasColumn('hotels', 'reviews_service_enabled')) {
                $table->boolean('reviews_service_enabled')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasColumn('hotels', 'chat_service_enabled')) {
                $table->dropColumn('chat_service_enabled');
            }
            if (Schema::hasColumn('hotels', 'checkin_service_enabled')) {
                $table->dropColumn('checkin_service_enabled');
            }
            if (Schema::hasColumn('hotels', 'reviews_service_enabled')) {
                $table->dropColumn('reviews_service_enabled');
            }
        });
    }
};
