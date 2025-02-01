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
            $table->decimal('averagePrice', 10, 2)->default(0);
            $table->decimal('occupancyRate', 5, 2)->default(0);
            $table->decimal('reputationIncrease', 5, 2)->default(0);
            $table->decimal('pricePerNightIncrease', 5, 2)->default(0);
            $table->decimal('occupancyRateIncrease', 5, 2)->default(0);
            $table->decimal('pricePerRoomPerMonth', 10, 2)->default(8.99);
            $table->decimal('implementationPrice', 10, 2)->default(900);
            $table->decimal('investmentInHoster', 10, 2)->default(0);
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
