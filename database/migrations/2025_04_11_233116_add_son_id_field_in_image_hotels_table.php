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
        Schema::table('images_hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('images_hotels', 'son_id')) {
                $table->unsignedBigInteger('son_id')->nullable()->constrained('images_hotels')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images_hotels', function (Blueprint $table) {
            if (Schema::hasColumn('images_hotels', 'son_id')) {
                $table->dropColumn('son_id');
            }
        });
    }
};
