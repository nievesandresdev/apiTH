<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hoster\QuerySettingsHosterResource;
use App\Http\Resources\QuerySettingsResource;
use App\Services\Hoster\Queries\QuerySettingsHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuerySettingsHosterController extends Controller
{
    public $service;

    function __construct(
        QuerySettingsHosterServices $_QuerySettingsHosterServices
    )
    {
        $this->service = $_QuerySettingsHosterServices;
    }

    public function getPreStaySettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new QuerySettingsHosterResource($model,['pre_stay_activate', 'pre_stay_comment']);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getPreStaySettings');
        }
    }
    
    public function updatePreStaySettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, ['pre_stay_activate','pre_stay_comment'], $request, 'pre-stay');
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updatePreStaySettings');
        }
    }

    public function getStayVeryGoodSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new QuerySettingsHosterResource($model,[
                'in_stay_verygood_request_activate','in_stay_verygood_response_title','in_stay_verygood_response_msg','in_stay_verygood_request_otas',
                'in_stay_verygood_no_request_comment_activate','in_stay_verygood_no_request_comment_msg','in_stay_verygood_no_request_thanks_title',
                'in_stay_verygood_no_request_thanks_msg'
            ]);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getStayVeryGoodSettings');
        }
    }

    public function getStayGoodSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new QuerySettingsHosterResource($model,[
                'in_stay_good_request_activate','in_stay_good_response_title','in_stay_good_response_msg','in_stay_good_request_otas',
                'in_stay_good_no_request_comment_activate','in_stay_good_no_request_comment_msg','in_stay_good_no_request_thanks_title',
                'in_stay_good_no_request_thanks_msg'
            ]);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getStayGoodSettings');
        }
    }

    public function getStayBadSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new QuerySettingsHosterResource($model,[
                'in_stay_bad_response_title','in_stay_bad_response_msg'
            ]);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getStayGoodSettings');
        }
    }

    public function updateStayVeryGoodSettings(Request $request){
        try {
            Log::info('testeo '.json_encode($request->all()));
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, [
                'in_stay_verygood_request_activate','in_stay_verygood_response_title','in_stay_verygood_response_msg','in_stay_verygood_request_otas',
                'in_stay_verygood_no_request_comment_activate','in_stay_verygood_no_request_comment_msg','in_stay_verygood_no_request_thanks_title',
                'in_stay_verygood_no_request_thanks_msg'
            ], $request, 'in-stay');
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateStayVeryGoodSettings');
        }
    }

    public function updateStayGoodSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, [
                'in_stay_good_request_activate','in_stay_good_response_title','in_stay_good_response_msg','in_stay_good_request_otas',
                'in_stay_good_no_request_comment_activate','in_stay_good_no_request_comment_msg','in_stay_good_no_request_thanks_title',
                'in_stay_good_no_request_thanks_msg'
            ], $request, 'in-stay');
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }       
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateStayGoodSettings');
        }
    }

    public function updateStayBadSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, [
                'in_stay_bad_response_title','in_stay_bad_response_msg'
            ], $request, 'in-stay');
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }       
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateStayBadSettings');
        }
    }
    
    public function getPostStayVeryGoodSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new QuerySettingsHosterResource($model,[
                'post_stay_verygood_response_title','post_stay_verygood_response_msg','post_stay_verygood_request_otas',
            ]);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getPostStayVeryGoodSettings');
        }
    }

    public function updatePostStayVeryGoodSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, [
                'post_stay_verygood_response_title','post_stay_verygood_response_msg','post_stay_verygood_request_otas',
            ], $request, 'post-stay');
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }       
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updatePostStayVeryGoodSettings');
        }
    }

    public function getPostStayGoodSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new QuerySettingsHosterResource($model,[
                'post_stay_good_request_activate','post_stay_good_response_title','post_stay_good_response_msg','post_stay_good_request_otas',
                'post_stay_good_no_request_comment_activate','post_stay_good_no_request_comment_msg','post_stay_good_no_request_thanks_title',
                'post_stay_good_no_request_thanks_msg'
            ]);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getPostStayGoodSettings');
        }
    }

    public function updatePostStayGoodSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, [
                'post_stay_good_request_activate','post_stay_good_response_title','post_stay_good_response_msg','post_stay_good_request_otas',
                'post_stay_good_no_request_comment_activate','post_stay_good_no_request_comment_msg','post_stay_good_no_request_thanks_title',
                'post_stay_good_no_request_thanks_msg'
            ], $request, 'post-stay');
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }       
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updatePostStayGoodSettings');
        }
    }

    public function getPostStayBadSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new QuerySettingsHosterResource($model,[
                'post_stay_bad_response_title','post_stay_bad_response_msg'
            ]);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getPostStayBadSettings');
        }
    }

    public function updatePostStayBadSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, [
                'post_stay_bad_response_title','post_stay_bad_response_msg'
            ], $request, 'post-stay');
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);     
            }       
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updatePostStayBadSettings');
        }
    }
}
