<?php
namespace Database\Seeders;

use App\Models\QuerySetting;
use Illuminate\Database\Seeder;

class UpdateQuerySettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = QuerySetting::all();
        $default = queriesTextDefault();
        foreach($settings as $setting){
            //in-stay
            $setting->in_stay_thanks_good = $default->in_stay_thanks_good;
            $setting->in_stay_assessment_good = $default->in_stay_assessment_good;
            $setting->in_stay_assessment_good_activate = $default->in_stay_assessment_good_activate;
            $setting->in_stay_thanks_normal = $default->in_stay_thanks_normal;
            $setting->in_stay_assessment_normal = $default->in_stay_assessment_normal;
            $setting->in_stay_comment = $default->in_stay_comment;
            //post-stay
            $setting->post_stay_thanks_good = $default->post_stay_thanks_good;
            $setting->post_stay_assessment_good = $default->post_stay_assessment_good;
            $setting->post_stay_assessment_good_activate = $default->post_stay_assessment_good_activate;
            $setting->post_stay_thanks_normal = $default->post_stay_thanks_normal;
            $setting->post_stay_assessment_normal = $default->post_stay_assessment_normal;
            $setting->post_stay_comment = $default->post_stay_comment;
    
            $setting->save();
        }
    }
}
