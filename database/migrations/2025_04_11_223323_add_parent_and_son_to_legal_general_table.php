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
        Schema::table('legal_generals', function (Blueprint $table) {
            if(!Schema::hasColumn('legal_generals', 'son_id')){
                $table->unsignedBigInteger('son_id')->nullable();
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_general', function (Blueprint $table) {
            if(Schema::hasColumn('legal_generals', 'son_id')){
                $table->dropColumn('son_id');
            }
        });
    }
};
