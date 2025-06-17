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
            if (Schema::hasColumn('query_settings', 'pre_stay_thanks')) {
                $table->dropColumn('pre_stay_thanks');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_activate')) {
                $table->dropColumn('in_stay_activate');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_thanks_good')) {
                $table->dropColumn('in_stay_thanks_good');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_assessment_good_activate')) {
                $table->dropColumn('in_stay_assessment_good_activate');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_assessment_good')) {   
                $table->dropColumn('in_stay_assessment_good');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_thanks_normal')) {
                $table->dropColumn('in_stay_thanks_normal');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_assessment_normal_activate')) {    
                $table->dropColumn('in_stay_assessment_normal_activate');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_assessment_normal')) {
                $table->dropColumn('in_stay_assessment_normal');
            }
            if (Schema::hasColumn('query_settings', 'in_stay_comment')) {
                $table->dropColumn('in_stay_comment');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_thanks_good')) {
                $table->dropColumn('post_stay_thanks_good');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_assessment_good_activate')) {
                $table->dropColumn('post_stay_assessment_good_activate');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_assessment_good')) {
                $table->dropColumn('post_stay_assessment_good');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_thanks_normal')) {
                $table->dropColumn('post_stay_thanks_normal');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_assessment_normal_activate')) {
                $table->dropColumn('post_stay_assessment_normal_activate');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_assessment_normal')) {
                $table->dropColumn('post_stay_assessment_normal');
            }
            if (Schema::hasColumn('query_settings', 'post_stay_comment')) {
                $table->dropColumn('post_stay_comment');
            }
            if (Schema::hasColumn('query_settings', 'notify_to_hoster')) {
                $table->dropColumn('notify_to_hoster');
            }
            if (Schema::hasColumn('query_settings', 'email_notify_new_feedback_to')) {
                $table->dropColumn('email_notify_new_feedback_to');
            }
            if (Schema::hasColumn('query_settings', 'email_notify_pending_feedback_to')) {
                $table->dropColumn('email_notify_pending_feedback_to');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('query_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('query_settings', 'pre_stay_thanks')) {
                $table->text('pre_stay_thanks')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_activate')) {
                $table->boolean('in_stay_activate')->default(true);
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_thanks_good')) {
                $table->text('in_stay_thanks_good')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_assessment_good_activate')) {
                $table->boolean('in_stay_assessment_good_activate')->default(true);
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_assessment_good')) {
                $table->text('in_stay_assessment_good')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_thanks_normal')) {
                $table->text('in_stay_thanks_normal')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_assessment_normal_activate')) {
                $table->boolean('in_stay_assessment_normal_activate')->default(true);
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_assessment_normal')) {
                $table->text('in_stay_assessment_normal')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'in_stay_comment')) {
                $table->text('in_stay_comment')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_thanks_good')) {
                $table->text('post_stay_thanks_good')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_assessment_good_activate')) {
                $table->boolean('post_stay_assessment_good_activate')->default(true);
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_assessment_good')) {
                $table->text('post_stay_assessment_good')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_thanks_normal')) {
                $table->text('post_stay_thanks_normal')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_assessment_normal_activate')) {
                $table->boolean('post_stay_assessment_normal_activate')->default(true);
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_assessment_normal')) {
                $table->text('post_stay_assessment_normal')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'post_stay_comment')) {
                $table->text('post_stay_comment')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'notify_to_hoster')) {
                $table->text('notify_to_hoster')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'email_notify_new_feedback_to')) {
                $table->text('email_notify_new_feedback_to')->nullable();
            }
            if (!Schema::hasColumn('query_settings', 'email_notify_pending_feedback_to')) {
                $table->text('email_notify_pending_feedback_to')->nullable();
            }
        });
    }
};
