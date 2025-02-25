<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('dossier_data', function (Blueprint $table) {
            $table->integer('averagePrice')->change();
            $table->integer('occupancyRate')->change();
            $table->integer('pricePerNightIncrease')->change();
            $table->integer('occupancyRateIncrease')->change();
        });
    }

    public function down()
    {
        Schema::table('dossier_data', function (Blueprint $table) {
            $table->float('averagePrice')->change();
            $table->float('occupancyRate')->change();
            $table->float('pricePerNightIncrease')->change();
            $table->float('occupancyRateIncrease')->change();
        });
    }
};
