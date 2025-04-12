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
        Schema::table('chat_setting_language', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_setting_language', 'son_id')) {
                $table->unsignedBigInteger('son_id')->constrained('chat_setting_language')->nullable()->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_setting_language', function (Blueprint $table) {
            if (Schema::hasColumn('chat_setting_language', 'son_id')) {
                $table->dropForeign(['son_id']);
                $table->dropColumn('son_id');
            }
        });
    }
};
