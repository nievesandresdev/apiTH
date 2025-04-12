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
        Schema::table('image_galleries', function (Blueprint $table) {
            if (!Schema::hasColumn('image_galleries', 'son_id')) {
                $table->unsignedBigInteger('son_id')->nullable()->constrained('image_galleries')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_galleries', function (Blueprint $table) {
            if (Schema::hasColumn('image_galleries', 'son_id')) {
                $table->dropColumn('son_id');
            }
        });
    }
};
