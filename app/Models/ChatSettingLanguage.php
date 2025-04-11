<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSettingLanguage extends Model
{
    protected $table = 'chat_setting_language';
    protected $fillable = ['chat_setting_id', 'language_id', 'son_id'];
    
    
}
