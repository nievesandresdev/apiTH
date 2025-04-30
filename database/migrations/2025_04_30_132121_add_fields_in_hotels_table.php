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
            if (!Schema::hasColumn('hotels', 'contact_email')) {
                $table->string('contact_email')->nullable();
            }
            if (!Schema::hasColumn('hotels', 'contact_whatsapp_number')) {
                $table->string('contact_whatsapp_number')->nullable();
            }
            if (!Schema::hasColumn('hotels', 'show_contact')) {
                $table->boolean('show_contact')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasColumn('hotels', 'contact_email')) {
                $table->dropColumn('contact_email');
            }
            if (Schema::hasColumn('hotels', 'contact_whatsapp_number')) {
                $table->dropColumn('contact_whatsapp_number');
            }
            if (Schema::hasColumn('hotels', 'show_contact')) {
                $table->dropColumn('show_contact');
            }
        });
    }
};
