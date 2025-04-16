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
        Schema::table('reward_stays', function (Blueprint $table) {
            if (!Schema::hasColumn('reward_stays', 'son_id')) {
                $table->unsignedBigInteger('son_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_stays', function (Blueprint $table) {
            if (Schema::hasColumn('reward_stays', 'son_id')) {
                $table->dropColumn('son_id');
            }
        });
    }
};
