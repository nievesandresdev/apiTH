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
        Schema::table('hotel_translates', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_translates', 'son_id')) {
                $table->unsignedBigInteger('son_id')->nullable();
                $table->foreign('son_id')->references('id')->on('hotel_translates')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_translates', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_translates', 'son_id')) {
                $table->dropForeign(['son_id']);
                $table->dropColumn('son_id');
            }
        });
    }
};
