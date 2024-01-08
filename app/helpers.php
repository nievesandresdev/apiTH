<?php

use App\Utils\Enums\EnumResponse;
use App\Utils\Enums\InventoryError;

if (!function_exists('bodyResponseRequest')) {
    /**
     * This function a implement custom response request JSON in Playbox.
     *
     * @param string $codeResp value of code transaction
     * @param array|string $data value of data result
     * @param string|null $customMessage value of custom message
     * @param string|null $methodException value of method exception
     * @return \Illuminate\Http\JsonResponse
     */
    function bodyResponseRequest($codeResp, $data = [], $customMessage = null, $methodException = null)
    {

        switch ($codeResp) {

            // Status 200
            case EnumResponse::SUCCESS:

                return response()->json([
                    'ok' => true,
                    'status' => \Illuminate\Http\Response::HTTP_OK,
                    'title' => __('response.success'),
                    'message' =>  $customMessage ?? __('response.success_long'),
                    'data' => $data
                ], \Illuminate\Http\Response::HTTP_OK);

                break;

            // Status 200
            case EnumResponse::SUCCESS_OK:

                return response()->json([
                    'ok' => true,
                    'status' => \Illuminate\Http\Response::HTTP_OK,
                    'title' => __('response.success'),
                    'message' =>  $customMessage ?? __('response.success_long'),
                ], \Illuminate\Http\Response::HTTP_OK);

                break;

            // Status 201
            case EnumResponse::CREATE_SUCCESS:
            
                return response()->json([
                    'ok' => true,
                    'status' => \Illuminate\Http\Response::HTTP_CREATED,  
                    'title' =>  __('response.create_success'),
                    'message' =>  $customMessage ?? __('response.create_success_long'),
                    'data' => $data
                ], \Illuminate\Http\Response::HTTP_CREATED);

                break;

            // Status 202
            case EnumResponse::ACCEPTED:
            
                return response()->json([
                    'ok' => true,
                    'status' => \Illuminate\Http\Response::HTTP_ACCEPTED ,
                    'title' => __('response.accepted'),
                    'message' =>   $customMessage ?? __('response.accepted_long'),
                    'data' => $data
                ], \Illuminate\Http\Response::HTTP_ACCEPTED );

                break;

            // Status 204
            case EnumResponse::NO_CONTENT:
            
                return response()->json([
                    'ok' => false,
                    'status' => \Illuminate\Http\Response::HTTP_NO_CONTENT ,
                    'title' => __('response.no_content'),
                    'message' => __('response.no_content_long')
                ], \Illuminate\Http\Response::HTTP_NO_CONTENT );

                break;

            // Status 400
            case EnumResponse::BAD_REQUEST:
                \Log::error('BAD_REQUEST', ['exception'=>$data]);
                return response()->json([
                    'ok' => false,
                    'status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST,
                    'title' => __('response.bad_request'),
                    'message' => __('response.bad_request_long'),
                    'motives'=> $data
                ], \Illuminate\Http\Response::HTTP_BAD_REQUEST);

                break;

            // Status 400
            case EnumResponse::DUPLICATE_ENTRY:

                \Log::error('DUPLICATE_ENTRY', ['exception'=>$data]);

                return response()->json([
                    'ok' => false,
                    'status'=>\Illuminate\Http\Response::HTTP_BAD_REQUEST,
                    'title'=> __('response.duplicate_entry'),
                    'message'=> __('response.duplicate_entry_long'),
                    'motives'=> $data
                ],\Illuminate\Http\Response::HTTP_BAD_REQUEST);

                break;

            // Status 401
            case EnumResponse::UNAUTHORIZED :
                \Log::error('UNAUTHORIZED', ['exception'=>$data]);
                return response()->json([
                    'ok' => false,
                    'status' => \Illuminate\Http\Response::HTTP_UNAUTHORIZED ,
                    'title' => __('response.unauthorized'),
                    'message'=>  $customMessage ?? __('response.unauthorized_long'),
                    'motives'=> $data
                ], \Illuminate\Http\Response::HTTP_UNAUTHORIZED );

                break;

            // Status 403
            case EnumResponse::FORBIDDEN :
                \Log::error('FORBIDDEN', ['exception'=>$data]);
                return response()->json([
                    'ok' => false,
                    'status' => \Illuminate\Http\Response::HTTP_FORBIDDEN ,
                    'title' => __('response.forbidden'),
                    'message'=> __('response.forbidden_long'),
                    'motives'=> $data
                ], \Illuminate\Http\Response::HTTP_FORBIDDEN );

                break;

            // Status 404
            case EnumResponse::NOT_FOUND :
                \Log::error('NOT_FOUND', ['exception'=>$data]);
                return response()->json([
                    'ok' => false,
                    'status' => \Illuminate\Http\Response::HTTP_NOT_FOUND,
                    'title' => __('response.not_found'),
                    'message'=> __('response.not_found_long'),
                    'motives'=> $data
                ], \Illuminate\Http\Response::HTTP_NOT_FOUND);

                break;

            // Status 422
            case EnumResponse::UNPROCESSABLE_ENTITY:
                \Log::error('UNPROCESSABLE_ENTITY', ['exception'=>$data]);

                return response()->json([
                    'ok' => false,
                    'status'=>\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY,
                    'title'=>__('response.unprocessable_entity'),
                    'message'=> __('response.unprocessable_entity_long'),
                    'motives'=> $data
                ],\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);

                break;

            // Status 500
            case EnumResponse::INTERNAL_SERVER_ERROR:

                \Log::error('INTERNAL_SERVER_ERROR', ['exception'=>$data]);

                return response()->json([
                    'ok' => false,
                    'status'=>\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR,
                    'title'=>__('response.internal_server_error'),
                    'message'=> __('response.internal_server_error_long'),
                    'motives'=> $data
                ],\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

                break;

            // Status 501
            case EnumResponse::NOT_IMPLEMENTED:
                \Log::error('NOT_IMPLEMENTED', ['exception'=>$data]);

                return response()->json([
                    'ok' => false,
                    'status'=>\Illuminate\Http\Response::HTTP_NOT_IMPLEMENTED ,
                    'title'=>__('response.not_implemented'),
                    'message'=> __('response.not_implemented_long'),
                    'motives'=> $data
                ],\Illuminate\Http\Response::HTTP_NOT_IMPLEMENTED );

                break;

            // Status 503
            case EnumResponse::SERVICE_UNAVAILABLE:
                \Log::error('SERVICE_UNAVAILABLE', ['exception'=>$data]);

                return response()->json([
                    'ok' => false,
                    'status'=>\Illuminate\Http\Response::HTTP_SERVICE_UNAVAILABLE ,
                    'title'=>__('response.service_unavailable'),
                    'message'=> __('response.service_unavailable_long'),
                    'motives'=> $data
                ],\Illuminate\Http\Response::HTTP_SERVICE_UNAVAILABLE );

                break;
            case EnumResponse::ERROR:

                \Log::error('ERROR', ['exception'=>$data]);

                return response()->json([
                    'ok' => false,
                    'status'=>\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR,
                    'title'=> __('response.error'),
                    'message'=> __('response.error_long'),
                    'methodException'=> $methodException
                ],\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

                break;
        }
    }
}

/**
* Custom messages response json request in Playbox.
*
* @author David Rivero <david.dotworkers@gmail.com>
*/
if (!function_exists('responseRequest')) {
    /**
     * This function a implement custom response request JSON in My Community 
     * 
     * @param string $codeResp
     * @param array|null  $data
     * @param string|null  $code_error
     * @return \Illuminate\Http\JsonResponse
     */
    function responseRequest($codeResp, $data = null, $code_error = null)
    {

        switch ($codeResp) {

            case EnumResponse::SUCCESS:

                return response()->json([
                    'status' => \Illuminate\Http\Response::HTTP_OK,
                    'title' => __('response.response_success'),
                    'message'=> __('response.response_success_long'),
                    'data' => $data
                ], \Illuminate\Http\Response::HTTP_OK);

                break;

            case EnumResponse::POST_SUCCESS:
            
                return response()->json([
                    'status' => \Illuminate\Http\Response::HTTP_OK,
                    'title' => __('response.response_post_success'),
                    'message'=> __('response.response_post_success_long'),
                ], \Illuminate\Http\Response::HTTP_OK);

                break;

            case EnumResponse::FAILED:

                return response()->json(
                    InventoryError::getErrorStatus($code_error), 
                    \Illuminate\Http\Response::HTTP_OK
                );

                break;

            case EnumResponse::CUSTOM_FAILED:

                return response()->json(
                    InventoryError::getErrorStatus($code_error, $data),
                    \Illuminate\Http\Response::HTTP_OK
                );

                break;

        }
    }
}

if (!function_exists('localeCurrent')) {
    function localeCurrent() {
        return app()->getLocale();
    }
}