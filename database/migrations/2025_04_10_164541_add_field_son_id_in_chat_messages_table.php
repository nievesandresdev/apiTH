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
        Schema::table('chat_messages', function (Blueprint $table) {
            if(!Schema::hasColumn('chat_messages', 'son_id')){
                $table->foreignId('son_id')->nullable()->constrained('chat_messages')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if(Schema::hasColumn('chat_messages', 'son_id')){
                $table->dropForeign(['son_id']);
                $table->dropColumn('son_id');
            }
        });
    }
};
