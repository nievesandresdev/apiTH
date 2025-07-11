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

        Schema::table('facility_hosters', function (Blueprint $table) {
            $table->string('document')->nullable();
            $table->string('document_file')->nullable();
            $table->string('text_document_button')->nullable();
            $table->string('link_document_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_hosters', function (Blueprint $table) {
            $table->dropColumn('document');
            $table->dropColumn('document_file');
            $table->dropColumn('text_document_button');
            $table->dropColumn('link_document_url');
        });
    }
};
