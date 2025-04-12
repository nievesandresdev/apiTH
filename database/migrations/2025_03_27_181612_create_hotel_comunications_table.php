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
        Schema::create('hotel_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('email');
            $table->boolean('welcome_email')->default(true);
            $table->boolean('pre_checkin_email')->default(false);
            $table->boolean('post_checkin_email')->default(true);
            $table->boolean('checkout_email')->default(false);
            $table->boolean('pre_checkout_email')->default(false);
            $table->boolean('new_chat_email')->default(false);
            $table->boolean('referent_email')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_comunications');
    }
};
