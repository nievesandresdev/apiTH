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
        Schema::table('guests', function (Blueprint $table) {
            if (!Schema::hasColumn('guests', 'second_lastname')) {
                $table->string('second_lastname')->nullable();
            }
            if (!Schema::hasColumn('guests', 'responsible_adult')) {
                $table->string('responsible_adult')->nullable();
            }
            if (!Schema::hasColumn('guests', 'kinship_relationship')) {
                $table->string('kinship_relationship')->nullable();
            }
            if (!Schema::hasColumn('guests', 'doc_support_number')) {
                $table->string('doc_support_number')->nullable();
            }
            if (!Schema::hasColumn('guests', 'postal_code')) {
                $table->string('postal_code')->nullable();
            }
            if (!Schema::hasColumn('guests', 'municipality')) {
                $table->string('municipality')->nullable();
            }
            if (!Schema::hasColumn('guests', 'country_address')) {
                $table->string('country_address')->nullable();
            }
            //
            if (!Schema::hasColumn('guests', 'complete_checkin_data')) {
                $table->boolean('complete_checkin_data')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            if (Schema::hasColumn('guests', 'second_lastname')) {
                $table->dropColumn('second_lastname');
            }
            if (Schema::hasColumn('guests', 'responsible_adult')) {
                $table->dropColumn('responsible_adult');
            }
            if (Schema::hasColumn('guests', 'kinship_relationship')) {
                $table->dropColumn('kinship_relationship');
            }
            if (Schema::hasColumn('guests', 'doc_support_number')) {
                $table->dropColumn('doc_support_number');
            }
            if (Schema::hasColumn('guests', 'postal_code')) {
                $table->dropColumn('postal_code');
            }
            if (Schema::hasColumn('guests', 'municipality')) {
                $table->dropColumn('municipality');
            }
            if (Schema::hasColumn('guests', 'country_address')) {
                $table->dropColumn('country_address');
            }
            //
            if (Schema::hasColumn('guests', 'complete_checkin_data')) {
                $table->dropColumn('complete_checkin_data');
            }
        });
    }
};
