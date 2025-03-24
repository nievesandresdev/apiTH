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
        Schema::table('dossier_data', function (Blueprint $table) {
            $table->decimal('reputationIncrease', 5, 1)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossier_data', function (Blueprint $table) {
            $table->decimal('reputationIncrease', 5, 2)->default(0)->change();
        });
    }
};

