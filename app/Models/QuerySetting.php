<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuerySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'pre_stay_activate',
        'pre_stay_thanks',
        'pre_stay_comment',
        'in_stay_activate',
        'in_stay_thanks_good',
        'in_stay_assessment_good_activate',
        'in_stay_assessment_good',
        'in_stay_thanks_normal',
        'in_stay_assessment_normal_activate',
        'in_stay_assessment_normal',
        'in_stay_comment',
        'post_stay_thanks_good',
        'post_stay_assessment_good_activate',
        'post_stay_assessment_good',
        'post_stay_thanks_normal',
        'post_stay_assessment_normal_activate',
        'post_stay_assessment_normal',
        'post_stay_comment',
        'notify_to_hoster',
        'email_notify_new_feedback_to',
        'email_notify_pending_feedback_to',
    ];
    
    protected $casts = [
        'pre_stay_thanks' => 'array',
        'pre_stay_comment' => 'array',
        'in_stay_thanks_good' => 'array',
        'in_stay_assessment_good' => 'array',
        'in_stay_thanks_normal' => 'array',
        'in_stay_assessment_normal' => 'array',
        'in_stay_comment' => 'array',
        'post_stay_thanks_good' => 'array',
        'post_stay_assessment_good' => 'array',
        'post_stay_thanks_normal' => 'array',
        'post_stay_assessment_normal' => 'array',
        'post_stay_comment' => 'array',
        'notify_to_hoster' => 'array',
        'email_notify_new_feedback_to' => 'array',
        'email_notify_pending_feedback_to' => 'array',
    ];

    protected $attributes = [
        'in_stay_assessment_good' => '[]',
        'in_stay_assessment_normal' => '[]',
        'post_stay_assessment_good' => '[]',
        'post_stay_assessment_normal' => '[]',
    ];

    public function getPreStayActivateAttribute($value)
    {
        return boolval($value);
    }

    public function getInStayActivateAttribute($value)
    {
        return boolval($value);
    }

    public function getInStayAssessmentGoodActivateAttribute($value)
    {
        return boolval($value);
    }

    public function getInStayAssessmentNormalActivateAttribute($value)
    {
        return boolval($value);
    }

    public function getPostStayAssessmentGoodActivateAttribute($value)
    {
        return boolval($value);
    }

    public function getPostStayAssessmentNormalActivateAttribute($value)
    {
        return boolval($value);
    }
    

}
