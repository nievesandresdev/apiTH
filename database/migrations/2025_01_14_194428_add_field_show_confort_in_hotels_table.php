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
            if (!Schema::hasColumn('hotels', 'show_confort')) {
                $table->boolean('show_confort')->default(false);
            }
            if (!Schema::hasColumn('hotels', 'show_transport')) {
                $table->boolean('show_transport')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasColumn('hotels', 'show_confort')) {
                $table->dropColumn('show_confort');
            }
            if (Schema::hasColumn('hotels', 'show_transport')) {
                $table->dropColumn('show_transport');
            }
        });
    }
};
