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
        Schema::table('password_otas', function (Blueprint $table) {
            if (!Schema::hasColumn('password_otas', 'recovery_token')) {
                $table->string('recovery_token')->nullable();

            }
            if (!Schema::hasColumn('password_otas', 'token_expires_at')) {
                $table->timestamp('token_expires_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_otas', function (Blueprint $table) {
            if (Schema::hasColumn('password_otas', 'recovery_token')) {
                $table->dropColumn('recovery_token');
            }
            if (Schema::hasColumn('password_otas', 'token_expires_at')) {
                $table->dropColumn('token_expires_at');
            }
        });
    }
};
