<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StayNotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'unfilled_check_platform',
        'unfilled_check_email',
        'create_check_email',
        'create_msg_email',
        'create_lang_email',
        'create_check_sms',
        'create_msg_sms',
        'create_lang_sms',
        'arrival_check_email',
        'arrival_msg_email',
        'arrival_lang_email',
        'arrival_check_sms',
        'arrival_msg_sms',
        'arrival_lang_sms',
        'preout_check_email',
        'preout_msg_email',
        'preout_lang_email',
        'preout_check_sms',
        'preout_msg_sms',
        'preout_lang_sms',
        'chat_hoster',
        'chat_guest',
        'guestcreate_check_email',
        'guestcreate_msg_email',
        'guestinvite_check_email',
        'guestinvite_msg_email'
    ];

    protected $casts = [
        'chat_hoster' => 'array',
        'chat_guest' => 'array',
        'create_msg_email' => 'array',
        'create_msg_sms' => 'array',
        'guestcreate_msg_email' => 'array',
        'guestinvite_msg_email' => 'array'
    ];    


            

            
            
            
            
            
            

            
            
            
            
            
            

            
            
            
            
            
            
}
