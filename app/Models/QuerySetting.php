<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuerySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        //////////////////////
        'pre_stay_activate',
        'pre_stay_comment',
        //////////////////////
        'in_stay_verygood_request_activate',
        'in_stay_verygood_response_title',
        'in_stay_verygood_response_msg',
        'in_stay_verygood_request_otas',
        'in_stay_verygood_no_request_comment_activate',
        'in_stay_verygood_no_request_comment_msg',
        'in_stay_verygood_no_request_thanks_title',
        'in_stay_verygood_no_request_thanks_msg',
        //
        'in_stay_good_request_activate',
        'in_stay_good_response_title',
        'in_stay_good_response_msg',
        'in_stay_good_request_otas',
        'in_stay_good_no_request_comment_activate',
        'in_stay_good_no_request_comment_msg',
        'in_stay_good_no_request_thanks_title',
        'in_stay_good_no_request_thanks_msg',
        //
        'in_stay_bad_response_title',
        'in_stay_bad_response_msg',
        //////////////////////
        'post_stay_verygood_response_title',
        'post_stay_verygood_response_msg',
        'post_stay_verygood_request_otas',
        //
        'post_stay_good_request_activate',
        'post_stay_good_response_title',
        'post_stay_good_response_msg',
        'post_stay_good_request_otas',
        'post_stay_good_no_request_comment_activate',
        'post_stay_good_no_request_comment_msg',
        'post_stay_good_no_request_thanks_title',
        'post_stay_good_no_request_thanks_msg',
        //
        'post_stay_bad_response_title',
        'post_stay_bad_response_msg',
    ];
    
    protected $casts = [
        'pre_stay_comment' => 'array',
        'in_stay_verygood_response_title' => 'array',
        'in_stay_verygood_response_msg' => 'array',
        'in_stay_verygood_request_otas' => 'array',
        'in_stay_verygood_no_request_comment_msg' => 'array',
        'in_stay_verygood_no_request_thanks_title' => 'array',
        'in_stay_verygood_no_request_thanks_msg' => 'array',
        //
        'in_stay_good_response_title' => 'array',
        'in_stay_good_response_msg' => 'array',
        'in_stay_good_request_otas' => 'array',
        'in_stay_good_no_request_comment_msg' => 'array',
        'in_stay_good_no_request_thanks_title' => 'array',
        'in_stay_good_no_request_thanks_msg' => 'array',
        //
        'in_stay_bad_response_title' => 'array',
        'in_stay_bad_response_msg' => 'array',
        //////////////////////
        'post_stay_verygood_response_title' => 'array',
        'post_stay_verygood_response_msg' => 'array',
        'post_stay_verygood_request_otas' => 'array',
        //
        'post_stay_good_response_title' => 'array',
        'post_stay_good_response_msg' => 'array',
        'post_stay_good_request_otas' => 'array',
        'post_stay_good_no_request_comment_msg' => 'array',
        'post_stay_good_no_request_thanks_title' => 'array',
        'post_stay_good_no_request_thanks_msg' => 'array',
        //
        'post_stay_bad_response_title' => 'array',
        'post_stay_bad_response_msg' => 'array',
    ];

    // protected $attributes = [
    //     'in_stay_assessment_good' => '[]'
    // ];

    public function getPreStayActivateAttribute($value)
    {
        return boolval($value);
    }

    public function getInStayVerygoodRequestActivateAttribute($value)
    {
        return boolval($value);
    }

    public function getInStayVerygoodNoRequestCommentActivateAttribute($value)
    {
        return boolval($value);
    }

    public function getInStayGoodRequestActivateAttribute($value)
    {
        return boolval($value);
    }

    public function getInStayGoodNoRequestCommentActivateAttribute($value)
    {
        return boolval($value);
    }

    public function getPostStayGoodRequestActivateAttribute($value)
    {
        return boolval($value);
    }
    
    public function getPostStayGoodNoRequestCommentActivateAttribute($value)
    {
        return boolval($value);
    }
}



// fields deletes   
//     'pre_stay_thanks',
//     'in_stay_activate',
//     'in_stay_thanks_good',
//     'in_stay_assessment_good_activate',
//     'in_stay_assessment_good',
//     'in_stay_thanks_normal',
//     'in_stay_assessment_normal_activate',
//     'in_stay_assessment_normal',
//     'in_stay_comment',
//     'post_stay_thanks_good',
//     'post_stay_assessment_good_activate',
//     'post_stay_assessment_good',
//     'post_stay_thanks_normal',
//     'post_stay_assessment_normal_activate',
//     'post_stay_assessment_normal',
//     'post_stay_comment',