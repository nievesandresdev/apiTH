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
        Schema::create('dossier_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->onDelete('cascade');
            $table->integer('tab_number');

            $table->integer('rooms')->default(0);
            $table->decimal('average_price', 10, 2)->default(0);
            $table->decimal('occupancy_rate', 5, 2)->default(0);
            $table->decimal('reputation_increase', 5, 2)->default(0);
            $table->decimal('price_per_night_increase', 5, 2)->default(0);
            $table->decimal('occupancy_rate_increase', 5, 2)->default(0);
            $table->decimal('price_per_room_per_month', 10, 2)->default(8.99);
            $table->decimal('implementation_price', 10, 2)->default(900);
            $table->decimal('investment_in_hoster', 10, 2)->default(0);
            $table->decimal('benefit', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossier_data');
    }
};
