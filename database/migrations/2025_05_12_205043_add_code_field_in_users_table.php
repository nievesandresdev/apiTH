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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'login_code')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('login_code')
                        ->nullable()
                        ->unique()
                        ->after('email');
                });            
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'login_code')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('login_code');
                });
            }
        });
    }
};
