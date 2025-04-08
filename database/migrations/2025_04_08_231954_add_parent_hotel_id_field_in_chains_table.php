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
        Schema::table('chains', function (Blueprint $table) {
            if (!Schema::hasColumn('chains', 'parent_id')) {
                $table->foreignId('parent_hotel_id')->nullable()->constrained('hotels')->onDelete('cascade')->comment('hotel padre');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chains', function (Blueprint $table) {
            if (Schema::hasColumn('chains', 'parent_hotel_id')) {
                $table->dropForeign(['parent_hotel_id']);
                $table->dropColumn('parent_hotel_id');
            }
        });
    }
};
