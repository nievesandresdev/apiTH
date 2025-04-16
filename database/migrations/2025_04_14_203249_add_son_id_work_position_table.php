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
        Schema::table('work_positions', function (Blueprint $table) {
            if (!Schema::hasColumn('work_positions', 'son_id')) {
                $table->unsignedBigInteger('son_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_positions', function (Blueprint $table) {
            if (Schema::hasColumn('work_positions', 'son_id')) {
                $table->dropColumn('son_id');
            }
        });
    }
};
