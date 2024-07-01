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
            if (!Schema::hasColumn('hotels', 'phone_optional')) {
                $table->string('phone_optional')->nullable();
            }
            if (!Schema::hasColumn('hotels', 'with_wifi')) {
                $table->boolean('with_wifi')->default(false);
            }
            if (!Schema::hasColumn('hotels', 'checkin_until')) {
                $table->string('checkin_until')->nullable();
            }
            if (!Schema::hasColumn('hotels', 'checkout_until')) {
                $table->string('checkout_until')->nullable();
            }
            if (!Schema::hasColumn('hotels', 'x_url')) {
                $table->string('x_url')->nullable();
            }
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasColumn('hotels', 'phone_optional')) {
                $table->dropColumn('phone_optional');
            }
            if (Schema::hasColumn('hotels', 'with_wifi')) {
                $table->dropColumn('with_wifi');
            }
            if (Schema::hasColumn('hotels', 'checkin_until')) {
                $table->dropColumn('checkin_until');
            }
            if (Schema::hasColumn('hotels', 'checkout_until')) {
                $table->dropColumn('checkout_until');
            }
            if (Schema::hasColumn('hotels', 'x_url')) {
                $table->dropColumn('x_url');
            }
        });
    }
};
