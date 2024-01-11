<?php

use App\Utils\Enums\EnumResponse;
use App\Utils\Enums\InventoryError;
use Illuminate\Http\Request;

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

if (! function_exists('settingsNotyStayDefault')) {
    function settingsNotyStayDefault(){
        return [
            'unfilled_check_platform' => true,
            'unfilled_check_email' => true,
            //
            'create_check_email' => true,
            'create_msg_email' => [
                    'es'=>'<p>¡Hola [nombre]!<br><br>¡Bienvenido al [nombre_del_hotel]. Queremos que disfrutes al máximo tu estancia, por eso te invitamos a explorar nuestra webapp exclusiva. Accede a [URL] y compártela con el resto de huéspedes de la estancia para conocer toda la información del hotel y una guía completa de la ciudad. Si necesitas ayuda, nuestro equipo está disponible. ¡Esperamos que tengas una experiencia increíble en nuestro hotel! <br><br>El equipo del [nombre_del_hotel].<p>',
                    'en'=>'<p>Hello [nombre]!<br><br>Welcome to [nombre_del_hotel]. We want you to enjoy your stay to the fullest, which is why we invite you to explore our exclusive webapp. Access [URL] and share it with the rest of the guests of the stay to find out all the hotel information and a complete guide to the city. If you need help, our team is available. We hope you have an amazing experience at our hotel! <br><br>The [nombre_del_hotel] team.<p>',
                    'fr'=>"<p>Bonjour [nombre] !<br><br>Bienvenue à [nombre_del_hotel]. Nous souhaitons que vous profitiez au maximum de votre séjour, c'est pourquoi nous vous invitons à explorer notre webapp exclusive. Accédez à [URL] et partagez-le avec le reste des invités du séjour pour découvrir toutes les informations de l'hôtel et un guide complet de la ville. Si vous avez besoin d'aide, notre équipe est disponible. Nous espérons que vous vivrez une expérience incroyable dans notre hôtel ! <br><br>L'équipe de [nombre_del_hotel].<p>"
            ],
            'create_lang_email' => 'es',
            'create_check_sms' => true,
            'create_msg_sms' => [
                'es'=>'¡Hola [nombre]! Bienvenido al [nombre_del_hotel]. Explora y comparte nuestra webapp en [URL]. ¡Disfruta tu estancia!',
                'en'=>'Hello [nombre]! Welcome to [nombre_del_hotel]. Explore and share our website at [URL]. Enjoy your stay!',
                'fr'=>"Bonjour [nombre]! Bienvenue à [nombre_del_hotel]. Explorez et partagez notre site Web à [URL]. Profitez de votre séjour!"
            ],
            'create_lang_sms' => 'es',
            //
            'guestcreate_check_email' => true,
            'guestcreate_msg_email' => [
                'es'=>'<p>¡Hola [nombre]!<br><br>¡Esperamos que  disfrutes tu estancia en el [nombre_del_hotel]! Te invitamos a compartir la webapp con el resto de huéspedes [URL]. Descubrirán detalles del hotel y una guía completa de la ciudad. ¡Estamos aquí para que disfrutes al máximo! <br><br>El equipo del [nombre_del_hotel].<p>',
                'en'=>'<p>Hello [nombre]!<br><br>We hope you enjoy your stay at [nombre_del_hotel]! We invite you to share the webapp with the rest of the guests [URL]. You will discover details of the hotel and a complete guide to the city. We are here for you to enjoy to the fullest! <br><br>The [nombre_del_hotel] team.<p>',
                'fr'=>"<p>Bonjour [nombre]!<br><br>Nous espérons que vous apprécierez votre séjour à [nombre_del_hotel]! Nous vous invitons à partager la webapp avec le reste des invités [URL]. Vous découvrirez les détails de l'hôtel et un guide complet de la ville. Nous sommes là pour que vous en profitiez au maximum ! <br><br>L'équipe de [nombre_del_hotel].<p>"
            ],
            //
            'guestinvite_check_email' => true,
            'guestinvite_msg_email' => [
                'es'=>'<p>¡Hola [nombre]!<br><br>Échale un vistazo a la webapp de [nombre_del_hotel], que está llena de información para hacer nuestra experiencia aún más completa. Accede a través de [URL]. Descubre detalles del hotel y una guía completa de la ciudad. ¡Disfrutarás al máximo!<p>',
                'en'=>'<p>Hello [nombre]!<br><br>Take a look at the [nombre_del_hotel] webapp, which is full of information to make our experience even more complete. Access through [URL]. Discover hotel details and a complete city guide. You will enjoy it to the fullest!<p>',
                'fr'=>"<p>Bonjour [nombre]!<br><br>Jetez un œil à la webapp [nombre_del_hotel], qui regorge d'informations pour rendre notre expérience encore plus complète. Accès via [URL]. Découvrez les détails de l'hôtel et un guide complet de la ville. Vous en profiterez pleinement!<p>"
            ],
            //
            'chat_hoster' => [
                'when_msg_received_guest'=>[
                    'via_platform'=>true,
                    'via_email'=>false,
                ],
                'when_not_answered'=>[
                    'via_platform'=>true,
                    'via_email'=>true,
                ],
            ],
            'chat_guest' => [
                'when_unread_message'=>[
                    'via_sms'=>true,
                    'via_email'=>false,
                ],
            ],
            //
            //no se usa por ahora
            'arrival_check_email' => true,//NO USANDO
            'arrival_msg_email' => '', //NO USANDO
            'arrival_lang_email' => 'es', //NO USANDO
            'arrival_check_sms' => false, //NO USANDO
            'arrival_msg_sms' => '', //NO USANDO 
            'arrival_lang_sms' => 'es', //NO USANDO
            'preout_check_email' => true, //NO USANDO
            'preout_msg_email' => '', //NO USANDO
            'preout_lang_email' => 'es', //NO USANDO
            'preout_check_sms' => false, //NO USANDO
            'preout_msg_sms' => '', //NO USANDO
            'preout_lang_sms' => 'es', //NO USANDO
        ];
    }
}

//prepara url mensaje con url del hotel
if (! function_exists('prepareMessage')) {
    //reemplaza las variables con datos para crear un mensaje personalizado para el huesped
    function prepareMessage($data,$hotel,$params_url = null){

        $link = url('webapp?e='.$data['stay_id'].'&g='.$data['guest_id'].'&lang='.$data['stay_lang']);
        $link =  includeSubdomainInUrlHuesped($link, $hotel);
        if($params_url){
            $link = $link.$params_url;
        }
        $msg = $data['msg_text'];
        $msg = str_replace('[nombre]', $data['guest_name'], $msg);
        $msg = str_replace('[nombre_del_hotel]', $data['hotel_name'], $msg);
        $msg = str_replace('[URL]', $link, $msg);
        // return $msg = googleTanslate($data['msg_lang'], $msg);
        return $msg;
    }
}

if (! function_exists('includeSubdomainInUrlHuesped')) {
    function includeSubdomainInUrlHuesped($url, $hotel){
        if (!$url || !$hotel) return;
        $production  = config('app.production');
        $url_base_huesped = $url;
        if ($production) {
            $host_parts =  explode('//', $url);
            $url_base_huesped = $host_parts[0].'//'.$hotel['subdomain'].'.'.$host_parts[1];
            return $url_base_huesped;
        }
        if (!$production) {
            $request = Request::create($url_base_huesped);
            $updated_url = $request->fullUrlWithQuery(['subdomain' => $hotel['subdomain']]);
            return $updated_url;
        }
    }
}

if (! function_exists('sendEventPusher')) {
    function sendEventPusher($channel,$event,$data){
        // $pusher = new Pusher(
        //     config('services.pusher.key'),
        //     config('services.pusher.secret'),
        //     config('services.pusher.id'),
        //     ['cluster' => config('services.pusher.cluster')]
        // );
        // $pusher->trigger($channel, $event, $data);
    }
}