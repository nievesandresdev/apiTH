<?php

namespace App\Services\Hoster\Queries;

use App\Jobs\TranslateGenericMultipleJob;
use App\Jobs\TranslateModelJob;
use App\Models\QuerySetting;
use App\Utils\Enums\EnumsQueries\QuerySettingsEnums;
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
                $default = QuerySettingsEnums::queriesTextDefault();
            }
            return $default;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updateSettings ($hotelId, $keysToSave, $newdata, $period = null) {
        try {
            $default = $this->getAll($hotelId);
            $save = QuerySetting::updateOrCreate(['hotel_id' => $hotelId],
                [
                    'pre_stay_activate' => in_array('pre_stay_activate', $keysToSave) ? $newdata->pre_stay_activate : $default->pre_stay_activate,
                    'pre_stay_comment' => in_array('pre_stay_comment', $keysToSave) ? $newdata->pre_stay_comment : $default->pre_stay_comment,
                    //////////////////////
                    'in_stay_verygood_request_activate' => in_array('in_stay_verygood_request_activate', $keysToSave) ? $newdata->in_stay_verygood_request_activate : $default->in_stay_verygood_request_activate,
                    'in_stay_verygood_response_title' => in_array('in_stay_verygood_response_title', $keysToSave) ? $newdata->in_stay_verygood_response_title : $default->in_stay_verygood_response_title,
                    'in_stay_verygood_response_msg' => in_array('in_stay_verygood_response_msg', $keysToSave) ? $newdata->in_stay_verygood_response_msg : $default->in_stay_verygood_response_msg,
                    'in_stay_verygood_request_otas' => in_array('in_stay_verygood_request_otas', $keysToSave) ? $newdata->in_stay_verygood_request_otas : $default->in_stay_verygood_request_otas,
                    'in_stay_verygood_no_request_comment_activate' => in_array('in_stay_verygood_no_request_comment_activate', $keysToSave) ? $newdata->in_stay_verygood_no_request_comment_activate : $default->in_stay_verygood_no_request_comment_activate,
                    'in_stay_verygood_no_request_comment_msg' => in_array('in_stay_verygood_no_request_comment_msg', $keysToSave) ? $newdata->in_stay_verygood_no_request_comment_msg : $default->in_stay_verygood_no_request_comment_msg,
                    'in_stay_verygood_no_request_thanks_title' => in_array('in_stay_verygood_no_request_thanks_title', $keysToSave) ? $newdata->in_stay_verygood_no_request_thanks_title : $default->in_stay_verygood_no_request_thanks_title,
                    'in_stay_verygood_no_request_thanks_msg' => in_array('in_stay_verygood_no_request_thanks_msg', $keysToSave) ? $newdata->in_stay_verygood_no_request_thanks_msg : $default->in_stay_verygood_no_request_thanks_msg,
                    //
                    'in_stay_good_request_activate' => in_array('in_stay_good_request_activate', $keysToSave) ? $newdata->in_stay_good_request_activate : $default->in_stay_good_request_activate,
                    'in_stay_good_response_title' => in_array('in_stay_good_response_title', $keysToSave) ? $newdata->in_stay_good_response_title : $default->in_stay_good_response_title,
                    'in_stay_good_response_msg' => in_array('in_stay_good_response_msg', $keysToSave) ? $newdata->in_stay_good_response_msg : $default->in_stay_good_response_msg,
                    'in_stay_good_request_otas' => in_array('in_stay_good_request_otas', $keysToSave) ? $newdata->in_stay_good_request_otas : $default->in_stay_good_request_otas,
                    'in_stay_good_no_request_comment_activate' => in_array('in_stay_good_no_request_comment_activate', $keysToSave) ? $newdata->in_stay_good_no_request_comment_activate : $default->in_stay_good_no_request_comment_activate,
                    'in_stay_good_no_request_comment_msg' => in_array('in_stay_good_no_request_comment_msg', $keysToSave) ? $newdata->in_stay_good_no_request_comment_msg : $default->in_stay_good_no_request_comment_msg,
                    'in_stay_good_no_request_thanks_title' => in_array('in_stay_good_no_request_thanks_title', $keysToSave) ? $newdata->in_stay_good_no_request_thanks_title : $default->in_stay_good_no_request_thanks_title,
                    'in_stay_good_no_request_thanks_msg' => in_array('in_stay_good_no_request_thanks_msg', $keysToSave) ? $newdata->in_stay_good_no_request_thanks_msg : $default->in_stay_good_no_request_thanks_msg,
                    //
                    'in_stay_bad_response_title' => in_array('in_stay_bad_response_title', $keysToSave) ? $newdata->in_stay_bad_response_title : $default->in_stay_bad_response_title,
                    'in_stay_bad_response_msg' => in_array('in_stay_bad_response_msg', $keysToSave) ? $newdata->in_stay_bad_response_msg : $default->in_stay_bad_response_msg,
                    //////////////////////
                    'post_stay_verygood_response_title' => in_array('post_stay_verygood_response_title', $keysToSave) ? $newdata->post_stay_verygood_response_title : $default->post_stay_verygood_response_title,
                    'post_stay_verygood_response_msg' => in_array('post_stay_verygood_response_msg', $keysToSave) ? $newdata->post_stay_verygood_response_msg : $default->post_stay_verygood_response_msg,
                    'post_stay_verygood_request_otas' => in_array('post_stay_verygood_request_otas', $keysToSave) ? $newdata->post_stay_verygood_request_otas : $default->post_stay_verygood_request_otas,
                    //
                    'post_stay_good_request_activate' => in_array('post_stay_good_request_activate', $keysToSave) ? $newdata->post_stay_good_request_activate : $default->post_stay_good_request_activate,
                    'post_stay_good_response_title' => in_array('post_stay_good_response_title', $keysToSave) ? $newdata->post_stay_good_response_title : $default->post_stay_good_response_title,
                    'post_stay_good_response_msg' => in_array('post_stay_good_response_msg', $keysToSave) ? $newdata->post_stay_good_response_msg : $default->post_stay_good_response_msg,
                    'post_stay_good_request_otas' => in_array('post_stay_good_request_otas', $keysToSave) ? $newdata->post_stay_good_request_otas : $default->post_stay_good_request_otas,
                    'post_stay_good_no_request_comment_activate' => in_array('post_stay_good_no_request_comment_activate', $keysToSave) ? $newdata->post_stay_good_no_request_comment_activate : $default->post_stay_good_no_request_comment_activate,
                    'post_stay_good_no_request_comment_msg' => in_array('post_stay_good_no_request_comment_msg', $keysToSave) ? $newdata->post_stay_good_no_request_comment_msg : $default->post_stay_good_no_request_comment_msg,
                    'post_stay_good_no_request_thanks_title' => in_array('post_stay_good_no_request_thanks_title', $keysToSave) ? $newdata->post_stay_good_no_request_thanks_title : $default->post_stay_good_no_request_thanks_title,
                    'post_stay_good_no_request_thanks_msg' => in_array('post_stay_good_no_request_thanks_msg', $keysToSave) ? $newdata->post_stay_good_no_request_thanks_msg : $default->post_stay_good_no_request_thanks_msg,
                    //
                    'post_stay_bad_response_title' => in_array('post_stay_bad_response_title', $keysToSave) ? $newdata->post_stay_bad_response_title : $default->post_stay_bad_response_title,
                    'post_stay_bad_response_msg' => in_array('post_stay_bad_response_msg', $keysToSave) ? $newdata->post_stay_bad_response_msg : $default->post_stay_bad_response_msg,
                ]
            );
            Log::info('save '.json_encode($save));
            $this->processTranslateTexts($newdata, $save, $period);
            return $save;
            
        } catch (\Exception $e) {
            return $e;
        }
    }
    public function updateTranslation($model, $translation) {
        Log::info('execute updateTranslation'. json_encode($translation));
        // Asegurarse de que $translation sea un arreglo
        $translationFormat = json_decode(json_encode($translation), true);
    
        foreach ($translationFormat as $key => &$categories) {
            foreach ($categories as $lang => &$details) {
                // Asegurarse de que 'text' existe antes de intentar accederlo
                if (isset($details['text'])) {
                    $details = $details['text'];
                }
            }
        }
        $model->pre_stay_comment = isset($translationFormat['pre_stay_comment']) ? $translationFormat['pre_stay_comment'] : $model->pre_stay_comment;
        //////////////////////
        $model->in_stay_verygood_response_title = isset($translationFormat['in_stay_verygood_response_title']) ? $translationFormat['in_stay_verygood_response_title'] : $model->in_stay_verygood_response_title;
        $model->in_stay_verygood_response_msg = isset($translationFormat['in_stay_verygood_response_msg']) ? $translationFormat['in_stay_verygood_response_msg'] : $model->in_stay_verygood_response_msg;
        $model->in_stay_verygood_no_request_comment_msg = isset($translationFormat['in_stay_verygood_no_request_comment_msg']) ? $translationFormat['in_stay_verygood_no_request_comment_msg'] : $model->in_stay_verygood_no_request_comment_msg;
        $model->in_stay_verygood_no_request_thanks_title = isset($translationFormat['in_stay_verygood_no_request_thanks_title']) ? $translationFormat['in_stay_verygood_no_request_thanks_title'] : $model->in_stay_verygood_no_request_thanks_title;
        $model->in_stay_verygood_no_request_thanks_msg = isset($translationFormat['in_stay_verygood_no_request_thanks_msg']) ? $translationFormat['in_stay_verygood_no_request_thanks_msg'] : $model->in_stay_verygood_no_request_thanks_msg;
        //
        $model->in_stay_good_response_title = isset($translationFormat['in_stay_good_response_title']) ? $translationFormat['in_stay_good_response_title'] : $model->in_stay_good_response_title;
        $model->in_stay_good_response_msg = isset($translationFormat['in_stay_good_response_msg']) ? $translationFormat['in_stay_good_response_msg'] : $model->in_stay_good_response_msg;
        $model->in_stay_good_no_request_comment_msg = isset($translationFormat['in_stay_good_no_request_comment_msg']) ? $translationFormat['in_stay_good_no_request_comment_msg'] : $model->in_stay_good_no_request_comment_msg;
        $model->in_stay_good_no_request_thanks_title = isset($translationFormat['in_stay_good_no_request_thanks_title']) ? $translationFormat['in_stay_good_no_request_thanks_title'] : $model->in_stay_good_no_request_thanks_title;
        $model->in_stay_good_no_request_thanks_msg = isset($translationFormat['in_stay_good_no_request_thanks_msg']) ? $translationFormat['in_stay_good_no_request_thanks_msg'] : $model->in_stay_good_no_request_thanks_msg;
        //
        $model->in_stay_bad_response_title = isset($translationFormat['in_stay_bad_response_title']) ? $translationFormat['in_stay_bad_response_title'] : $model->in_stay_bad_response_title;
        $model->in_stay_bad_response_msg = isset($translationFormat['in_stay_bad_response_msg']) ? $translationFormat['in_stay_bad_response_msg'] : $model->in_stay_bad_response_msg;
        //////////////////////
        $model->post_stay_verygood_response_title = isset($translationFormat['post_stay_verygood_response_title']) ? $translationFormat['post_stay_verygood_response_title'] : $model->post_stay_verygood_response_title;
        $model->post_stay_verygood_response_msg = isset($translationFormat['post_stay_verygood_response_msg']) ? $translationFormat['post_stay_verygood_response_msg'] : $model->post_stay_verygood_response_msg;
        //
        $model->post_stay_good_response_title = isset($translationFormat['post_stay_good_response_title']) ? $translationFormat['post_stay_good_response_title'] : $model->post_stay_good_response_title;
        $model->post_stay_good_response_msg = isset($translationFormat['post_stay_good_response_msg']) ? $translationFormat['post_stay_good_response_msg'] : $model->post_stay_good_response_msg;
        $model->post_stay_good_no_request_comment_msg = isset($translationFormat['post_stay_good_no_request_comment_msg']) ? $translationFormat['post_stay_good_no_request_comment_msg'] : $model->post_stay_good_no_request_comment_msg;
        $model->post_stay_good_no_request_thanks_title = isset($translationFormat['post_stay_good_no_request_thanks_title']) ? $translationFormat['post_stay_good_no_request_thanks_title'] : $model->post_stay_good_no_request_thanks_title;
        $model->post_stay_good_no_request_thanks_msg = isset($translationFormat['post_stay_good_no_request_thanks_msg']) ? $translationFormat['post_stay_good_no_request_thanks_msg'] : $model->post_stay_good_no_request_thanks_msg;
        //
        $model->post_stay_bad_response_title = isset($translationFormat['post_stay_bad_response_title']) ? $translationFormat['post_stay_bad_response_title'] : $model->post_stay_bad_response_title;
        $model->post_stay_bad_response_msg = isset($translationFormat['post_stay_bad_response_msg']) ? $translationFormat['post_stay_bad_response_msg'] : $model->post_stay_bad_response_msg;

        $model->save();
        Log::info('nueva traduccion guardada');
    }
    

    public function processTranslateTexts ($request, $model, $period){
        
        $pre_stay_comment = $request->pre_stay_comment['es'] ?? null;
        $arrToTranslate = ['pre_stay_comment' => $pre_stay_comment];
        if($period == 'in-stay'){
            $in_stay_verygood_response_title = $request->in_stay_verygood_response_title['es'] ?? null;
            $in_stay_verygood_response_msg = $request->in_stay_verygood_response_msg['es'] ?? null;
            $in_stay_verygood_no_request_comment_msg = $request->in_stay_verygood_no_request_comment_msg['es'] ?? null;
            $in_stay_verygood_no_request_thanks_title = $request->in_stay_verygood_no_request_thanks_title['es'] ?? null;
            $in_stay_verygood_no_request_thanks_msg = $request->in_stay_verygood_no_request_thanks_msg['es'] ?? null;
            //
            $in_stay_good_response_title = $request->in_stay_good_response_title['es'] ?? null;
            $in_stay_good_response_msg = $request->in_stay_good_response_msg['es'] ?? null;
            $in_stay_good_no_request_comment_msg = $request->in_stay_good_no_request_comment_msg['es'] ?? null;
            $in_stay_good_no_request_thanks_title = $request->in_stay_good_no_request_thanks_title['es'] ?? null;
            $in_stay_good_no_request_thanks_msg = $request->in_stay_good_no_request_thanks_msg['es'] ?? null;
            //
            $in_stay_bad_response_title = $request->in_stay_bad_response_title['es'] ?? null;
            $in_stay_bad_response_msg = $request->in_stay_bad_response_msg['es'] ?? null;
            //
            $arrToTranslate = [
                'in_stay_verygood_response_title' => $in_stay_verygood_response_title,
                'in_stay_verygood_response_msg' => $in_stay_verygood_response_msg,
                'in_stay_verygood_no_request_comment_msg' => $in_stay_verygood_no_request_comment_msg,
                'in_stay_verygood_no_request_thanks_title' => $in_stay_verygood_no_request_thanks_title,
                'in_stay_verygood_no_request_thanks_msg' => $in_stay_verygood_no_request_thanks_msg,
                //
                'in_stay_good_response_title' => $in_stay_good_response_title,
                'in_stay_good_response_msg' => $in_stay_good_response_msg,
                'in_stay_good_no_request_comment_msg' => $in_stay_good_no_request_comment_msg,
                'in_stay_good_no_request_thanks_title' => $in_stay_good_no_request_thanks_title,
                'in_stay_good_no_request_thanks_msg' => $in_stay_good_no_request_thanks_msg,
                //
                'in_stay_bad_response_title' => $in_stay_bad_response_title,
                'in_stay_bad_response_msg' => $in_stay_bad_response_msg,
            ];
        }
        if($period == 'post-stay'){
            $post_stay_verygood_response_title = $request->post_stay_verygood_response_title['es'] ?? null;
            $post_stay_verygood_response_msg = $request->post_stay_verygood_response_msg['es'] ?? null;
            //
            $post_stay_good_response_title = $request->post_stay_good_response_title['es'] ?? null;
            $post_stay_good_response_msg = $request->post_stay_good_response_msg['es'] ?? null;
            $post_stay_good_no_request_comment_msg = $request->post_stay_good_no_request_comment_msg['es'] ?? null;
            $post_stay_good_no_request_thanks_title = $request->post_stay_good_no_request_thanks_title['es'] ?? null;
            $post_stay_good_no_request_thanks_msg = $request->post_stay_good_no_request_thanks_msg['es'] ?? null;
            //
            $post_stay_bad_response_title = $request->post_stay_bad_response_title['es'] ?? null;
            $post_stay_bad_response_msg = $request->post_stay_bad_response_msg['es'] ?? null;
            $arrToTranslate = [
                'post_stay_verygood_response_title' => $post_stay_verygood_response_title,
                'post_stay_verygood_response_msg' => $post_stay_verygood_response_msg,
                //
                'post_stay_good_response_title' => $post_stay_good_response_title,
                'post_stay_good_response_msg' => $post_stay_good_response_msg,
                'post_stay_good_no_request_comment_msg' => $post_stay_good_no_request_comment_msg,
                'post_stay_good_no_request_thanks_title' => $post_stay_good_no_request_thanks_title,
                'post_stay_good_no_request_thanks_msg' => $post_stay_good_no_request_thanks_msg,
                //
                'post_stay_bad_response_title' => $post_stay_bad_response_title,
                'post_stay_bad_response_msg' => $post_stay_bad_response_msg,
            ];
        }
        
        TranslateGenericMultipleJob::dispatch($arrToTranslate, $this, $model, [], false);
    }

    
}
