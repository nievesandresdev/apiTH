<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('buttons_home');
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->boolean('buttons_home')->default(true)->after('chain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('buttons_home');
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->json('buttons_home')->nullable()->after('chain_id');
        });
    }
};
