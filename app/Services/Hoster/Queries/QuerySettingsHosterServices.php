<?php

namespace App\Services\Hoster\Queries;

use App\Jobs\TranslateModelJob;
use App\Models\QuerySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class QuerySettingsHosterServices {

    function __construct()
    {

    }

    public function getAll ($hotelId) {
        try {
            $default = QuerySetting::where('hotel_id',$hotelId)->first();
            if(!$default){
                $default = queriesTextDefault();
            }
            return $default;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updateSettings ($hotelId, $keysToSave, $newdata) {
        try {
            $default = $this->getAll($hotelId);

            $save = QuerySetting::updateOrCreate(['hotel_id' => $hotelId],
                [
                    'pre_stay_activate' => in_array('pre_stay_activate', $keysToSave) ? $newdata->pre_stay_activate : $default->pre_stay_activate,
                    'pre_stay_thanks' => in_array('pre_stay_thanks', $keysToSave) ? $newdata->pre_stay_thanks : $default->pre_stay_thanks,
                    'pre_stay_comment' => in_array('pre_stay_comment', $keysToSave) ? $newdata->pre_stay_comment : $default->pre_stay_comment,
                    'in_stay_activate' => in_array('in_stay_activate', $keysToSave) ? $newdata->in_stay_activate : $default->in_stay_activate,
                    'in_stay_thanks_good' => in_array('in_stay_thanks_good', $keysToSave) ? $newdata->in_stay_thanks_good : $default->in_stay_thanks_good,
                    'in_stay_thanks_normal' => in_array('in_stay_thanks_normal', $keysToSave) ? $newdata->in_stay_thanks_normal : $default->in_stay_thanks_normal,
                    'in_stay_comment' => in_array('in_stay_comment', $keysToSave) ? $newdata->in_stay_comment : $default->in_stay_comment,
                    'post_stay_thanks_good' => in_array('post_stay_thanks_good', $keysToSave) ? $newdata->post_stay_thanks_good : $default->post_stay_thanks_good,
                    'post_stay_thanks_normal' => in_array('post_stay_thanks_normal', $keysToSave) ? $newdata->post_stay_thanks_normal : $default->post_stay_thanks_normal,
                    'post_stay_comment' => in_array('post_stay_comment', $keysToSave) ? $newdata->post_stay_comment : $default->post_stay_comment,
                    'notify_to_hoster' => in_array('notify_to_hoster', $keysToSave) ? $newdata->notify_to_hoster : $default->notify_to_hoster,
                    'email_notify_new_feedback_to' => in_array('email_notify_new_feedback_to', $keysToSave) ? $newdata->email_notify_new_feedback_to : $default->email_notify_new_feedback_to,
                    'email_notify_pending_feedback_to' => in_array('email_notify_pending_feedback_to', $keysToSave) ? $newdata->email_notify_pending_feedback_to : $default->email_notify_pending_feedback_to,
                ]
            );
            $this->processTranslateStay($newdata, $save);
            return $save;
            
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updateTranslation ($model, $translation) {
        Log::info('updateTranslation result'. json_encode($translation));
        Log::info('$model'. json_encode($model));
        // $translation = collect($translation ?? []);

        // foreach ($translation as $lg => $value) {
        //     $value = $value->description ?? null;
        //     if ($lg == 'es') {
        //         $model->description = $value;
        //         $model->save();
        //     }
        //     $model->translations()->updateOrCreate(
        //         [
        //             'language' => $lg,
        //             'hotel_id' => $model->id
        //         ],
        //         [
        //             'description' => $value,
        //             'name' => $model->name,
        //             'zone' => $model->zone,
        //             'type' => $model->type
        //         ]
        //     );
        // }
    }

    public function processTranslateStay ($request, $hotelModel, $period = null) {
        
        $pre_stay_thanks = $request->pre_stay_thanks['es'];
        $pre_stay_comment = $request->pre_stay_comment['es'];
        Log::info('$pre_stay_thanks'. json_encode($pre_stay_thanks));
        $inputsTranslate = ['pre_stay_thanks' => $pre_stay_thanks,'pre_stay_comment' => $pre_stay_comment];
        $dirTemplateTranslate = 'translation/generic';    
        TranslateModelJob::dispatch($dirTemplateTranslate, $inputsTranslate, $this, $hotelModel);
    }
    
}
