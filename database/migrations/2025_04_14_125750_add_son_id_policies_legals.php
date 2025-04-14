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
        Schema::table('policie_legals', function (Blueprint $table) {
            if(!Schema::hasColumn('policie_legals', 'son_id')){
                $table->foreignId('son_id')->nullable()->constrained('policie_legals')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policie_legals', function (Blueprint $table) {
            if(Schema::hasColumn('policie_legals', 'son_id')){
                $table->dropForeign(['son_id']);
                $table->dropColumn('son_id');
            }
        });
    }
};
