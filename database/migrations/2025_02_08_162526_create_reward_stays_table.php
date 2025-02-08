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
        Schema::create('reward_stays', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('hotel_id')->constrained('hotels')->comment('hotel al que pertenece el reward');
            $table->integer('stay_id')->comment('id de la estancia');
            $table->foreignId('guest_id')->constrained('guests')->comment('huesped que invita');
            $table->foreignId('reward_id')->constrained('rewards');
            $table->boolean('used')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_stays');
    }
};
