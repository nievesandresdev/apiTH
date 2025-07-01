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
        Schema::table('query_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('query_settings', 'in_stay_verygood_request_activate')) {
                $table->boolean('in_stay_verygood_request_activate')->default(true);
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_verygood_response_title')) {
                $table->text('in_stay_verygood_response_title')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_verygood_response_msg')) {
                $table->text('in_stay_verygood_response_msg')->nullable();
            }

            if (!Schema::hasColumn('query_settings', 'in_stay_verygood_request_otas')) {
                $table->text('in_stay_verygood_request_otas')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_verygood_no_request_comment_activate')) {
                $table->boolean('in_stay_verygood_no_request_comment_activate')->default(true);
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_verygood_no_request_comment_msg')) {
                $table->text('in_stay_verygood_no_request_comment_msg')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_verygood_no_request_thanks_title')) {
                $table->text('in_stay_verygood_no_request_thanks_title')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_verygood_no_request_thanks_msg')) {
                $table->text('in_stay_verygood_no_request_thanks_msg')->nullable();
            }

            if (!Schema::hasColumn('query_settings', 'in_stay_good_request_activate')) {
                $table->boolean('in_stay_good_request_activate')->default(true);
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_good_response_title')) {
                $table->text('in_stay_good_response_title')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_good_response_msg')) {
                $table->text('in_stay_good_response_msg')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_good_request_otas')) {
                $table->text('in_stay_good_request_otas')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_good_no_request_comment_activate')) {
                $table->boolean('in_stay_good_no_request_comment_activate')->default(true);
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_good_no_request_comment_msg')) {
                $table->text('in_stay_good_no_request_comment_msg')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_good_no_request_thanks_title')) {
                $table->text('in_stay_good_no_request_thanks_title')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_good_no_request_thanks_msg')) {
                $table->text('in_stay_good_no_request_thanks_msg')->nullable();
            }

            if (!Schema::hasColumn('query_settings', 'in_stay_bad_response_title')) {
                $table->text('in_stay_bad_response_title')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_bad_response_msg')) {
                $table->text('in_stay_bad_response_msg')->nullable();
            }

            if (!Schema::hasColumn('query_settings', 'post_stay_verygood_response_title')) {
                $table->text('post_stay_verygood_response_title')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_verygood_response_msg')) {
                $table->text('post_stay_verygood_response_msg')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_verygood_request_otas')) {
                $table->text('post_stay_verygood_request_otas')->nullable();
            }

            if (!Schema::hasColumn('query_settings', 'post_stay_good_request_activate')) {
                $table->boolean('post_stay_good_request_activate')->default(true);
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_good_response_title')) {
                $table->text('post_stay_good_response_title')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_good_response_msg')) {
                $table->text('post_stay_good_response_msg')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_good_request_otas')) {
                $table->text('post_stay_good_request_otas')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_good_no_request_comment_activate')) {
                $table->boolean('post_stay_good_no_request_comment_activate')->default(true);
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_good_no_request_comment_msg')) {
                $table->text('post_stay_good_no_request_comment_msg')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_good_no_request_thanks_title')) {
                $table->text('post_stay_good_no_request_thanks_title')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_good_no_request_thanks_msg')) {
                $table->text('post_stay_good_no_request_thanks_msg')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_bad_response_title')) {
                $table->text('post_stay_bad_response_title')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_bad_response_msg')) {
                $table->text('post_stay_bad_response_msg')->nullable();
            }
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('query_settings', function (Blueprint $table) {
            if (Schema::hasColumn('query_settings', 'in_stay_verygood_request_activate')) {
                $table->dropColumn('in_stay_verygood_request_activate');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_verygood_response_title')) {
                $table->dropColumn('in_stay_verygood_response_title');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_verygood_response_msg')) {
                $table->dropColumn('in_stay_verygood_response_msg');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_verygood_request_otas')) {
                $table->dropColumn('in_stay_verygood_request_otas');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_verygood_no_request_comment_activate')) {
                $table->dropColumn('in_stay_verygood_no_request_comment_activate');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_verygood_no_request_comment_msg')) {
                $table->dropColumn('in_stay_verygood_no_request_comment_msg');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_verygood_no_request_thanks_title')) {
                $table->dropColumn('in_stay_verygood_no_request_thanks_title');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_verygood_no_request_thanks_msg')) {
                $table->dropColumn('in_stay_verygood_no_request_thanks_msg');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_good_request_activate')) {
                $table->dropColumn('in_stay_good_request_activate');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_good_response_title')) {
                $table->dropColumn('in_stay_good_response_title');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_good_response_msg')) {
                $table->dropColumn('in_stay_good_response_msg');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_good_request_otas')) {
                $table->dropColumn('in_stay_good_request_otas');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_good_no_request_comment_activate')) {
                $table->dropColumn('in_stay_good_no_request_comment_activate');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_good_no_request_comment_msg')) {
                $table->dropColumn('in_stay_good_no_request_comment_msg');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_good_no_request_thanks_title')) {
                $table->dropColumn('in_stay_good_no_request_thanks_title');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_bad_response_title')) {
                $table->dropColumn('in_stay_bad_response_title');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_bad_response_msg')) {
                $table->dropColumn('in_stay_bad_response_msg');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_verygood_response_title')) {
                $table->dropColumn('post_stay_verygood_response_title');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_verygood_response_msg')) {
                $table->dropColumn('post_stay_verygood_response_msg');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_verygood_request_otas')) {
                $table->dropColumn('post_stay_verygood_request_otas');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_good_request_activate')) {
                $table->dropColumn('post_stay_good_request_activate');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_good_response_title')) {
                $table->dropColumn('post_stay_good_response_title');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_good_response_msg')) {
                $table->dropColumn('post_stay_good_response_msg');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_good_request_otas')) {
                $table->dropColumn('post_stay_good_request_otas');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_good_no_request_comment_activate')) {
                $table->dropColumn('post_stay_good_no_request_comment_activate');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_good_no_request_comment_msg')) {
                $table->dropColumn('post_stay_good_no_request_comment_msg');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_good_no_request_thanks_title')) {
                $table->dropColumn('post_stay_good_no_request_thanks_title');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_good_no_request_thanks_msg')) {
                $table->dropColumn('post_stay_good_no_request_thanks_msg');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_bad_response_title')) {
                $table->dropColumn('post_stay_bad_response_title');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_bad_response_msg')) {
                $table->dropColumn('post_stay_bad_response_msg');
            }
        });
    }
};
