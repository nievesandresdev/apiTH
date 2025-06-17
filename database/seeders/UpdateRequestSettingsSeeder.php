<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequestSetting;

class UpdateRequestSettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = RequestSetting::all();
        $default = requestSettingsDefault();
        foreach($settings as $setting){
            //in-stay
            $setting->msg_title = $default->msg_title;
            $setting->msg_text = $default->msg_text;
            $setting->in_stay_msg_title = $default->in_stay_msg_title;
            $setting->in_stay_msg_text = $default->in_stay_msg_text;
    
            $setting->save();
        }
    }
}
