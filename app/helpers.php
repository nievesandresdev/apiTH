<?php

use App\Models\Language;
use App\Utils\Enums\EnumResponse;
use App\Utils\Enums\InventoryError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;
use Intervention\Image\Facades\Image as ImageManager;
use Intervention\Image\ImageManagerStatic as ImageManagerStatic;

use App\Models\ImagesHotels;
use App\Models\ImageGallery;
use Carbon\Carbon;
use Illuminate\Support\Str;


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


//lenguageName
if (!function_exists('lenguageName')) {
    function lenguageName($abbreviation){
        $language = Language::where('abbreviation', $abbreviation)->first();
        return $language->name ?? 'es';
    }
}

if (! function_exists('get_property_in_url')) {
    function get_property_in_url($url, $property){
        if (!$url) return;
        $parts = parse_url($url);
        $query_string = $parts['query'];
        parse_str($query_string, $query_array);
        $cid = $query_array[$property];
        return $cid;
    }
}

if (! function_exists('currentHotel')) {
    function currentHotel (){
        $user = auth()->user();
        if ($user) {
            if($user->hasRole('Associate') || $user->hasRole('Operator') || $user->hasRole('Administrator')){
                $hotel = null;
                if (session()->has('hotel_selected')) {
                    $hotel_session = session('hotel_selected');
                    $hotel = hotel::with(['images', 'language_names'])->find($hotel_session->id);
                }
                if(!$hotel){
                    $hotel = $user->hotel()->orderBy('hotels.id','ASC')->first();
                }
                if($hotel){
                    $hotel->load('images');
                }
                return $hotel;
            }
        }
        return null;
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

if (! function_exists('getAllLanguages')) {
    function getAllLanguages(){
        $lgs = Language::where('active', 1)->get()->pluck('abbreviation');
        return $lgs;
    }
}

if (! function_exists('settingsNotyStayDefault')) {
    function settingsNotyStayDefault(){

            $settings = new stdClass();
            $settings->unfilled_check_platform = true;
            $settings->unfilled_check_email = true;
            //
            $settings->create_check_email = true;
            //Mensaje al añadir un envío a un huésped al crear estancia
            $settings->create_msg_email = [
                'es' => 'Hola [nombre],<br>Nos alegra que utilices nuestra WebApp!<br><br>Aprovecha al máximo todo lo que te ofrecemos:<br>Conoce las instalaciones del hotel<br>Descubre sitios turísticos imperdibles<br>Disfruta con nuestras recomendaciones de restaurantes<br>Comunícate con nuestro personal utilizando el Chat<br><br>Estamos a tu disposición para brindarte la mejor experiencia en tu estancia. Gracias por elegirnos.',
                'en' => 'Hello [nombre],<br>We are glad you are using our WebApp!<br><br>pr full advantage of everything we offer you:<br>Get to know the hotel facilities<br>Discover unmissable tourist sites<br>Enjoy our restaurant recommendations<br>Communicate with our staff using the Chat<br><br>We are at your disposal to provide you with the best experience during your stay. Thank you for choosing us.',
                'fr' => 'Bonjour [nombre],<br>Nous sommes ravis que vous utilisiez notre WebApp!<br><br>Profitez pleinement de tout ce que nous vous offrons:<br>Découvrez les installations de l\'hôtel<br>Découvrez des sites touristiques incontournables<br>Profitez de nos recommandations de restaurants<br>Communiquez avec notre personnel en utilisant le Chat<br><br>Nous sommes à votre disposition pour vous offrir la meilleure expérience lors de votre séjour. Merci de nous avoir choisis.'
            ];
            $settings->create_lang_email = 'es';
            $settings->create_check_sms = true;
            $settings->create_msg_sms = [
                'es'=>'¡Hola [nombre]! Bienvenido al [nombre_del_hotel]. Explora y comparte nuestra webapp en [URL]. ¡Disfruta tu estancia!',
                'en'=>'Hello [nombre]! Welcome to [nombre_del_hotel]. Explore and share our website at [URL]. Enjoy your stay!',
                'fr'=>"Bonjour [nombre]! Bienvenue à [nombre_del_hotel]. Explorez et partagez notre site Web à [URL]. Profitez de votre séjour!"
            ];
            $settings->create_lang_sms = 'es';
            //
            $settings->guestcreate_check_email = true;
            //Mensaje cuando un huésped crea una estancia
            $settings->guestcreate_msg_email = [
                'es' => '<p>Estimado huésped,<br> Estamos encantados de darte la bienvenida a nuestra WebApp.<br>Esperamos que tu estancia sea única e inolvidable,<br>nuestro equipo está a tu disposición.</p>',
                'en'=>'<p>Dear guest,<br> We are delighted to welcome you to our WebApp.<br> We hope your stay is unique and unforgettable.<br> Our team is at your service.</p>',
                'fr'=>"<p><br> Nous espérons que votre séjour sera unique et inoubliable.<br> Notre équipe est à votre disposition.</p>",
                "pt" => "<p>Estimado hóspede,<br> Temos o prazer de o receber na nossa WebApp. <br>Esperamos que a sua estadia seja única e inesquecível,<br>a nossa equipa está ao seu dispor.</p>",
                "it" => "<p>Caro ospite,<br> Siamo lieti di darti il ​​benvenuto nella nostra WebApp.<br>Ci auguriamo che il tuo soggiorno sia unico e indimenticabile,<br>il nostro team è a tua disposizione.</p>",
                "de" => "<p>Lieber Gast,<br>Wir freuen uns, Sie in unserer WebApp begrüßen zu dürfen.<br>Wir hoffen, dass Ihr Aufenthalt einzigartig und unvergesslich wird,<br>unser Team steht Ihnen gerne zur Verfügung.</p>"
            ];
            //
            $settings->guestinvite_check_email = true;
            //Mensaje cuando un huésped invita a otro huésped cuando ya esta la estancia creada
            $settings->guestinvite_msg_email = [
                'es' => 'Hola [nombre],<br>¿Ya has probado la WebApp de [nombre_del_hotel]?<br><br>No necesitas descargarla, puedes ver información del hotel, obtener recomendaciones de lugares para visitar y hasta chatear con recepción!<br><br>Excelente acompañante para disfrutar al máximo nuestra estancia.',
                'en'=>'Hello [nombre],<br>Have you tried the WebApp of [nombre_del_hotel]?<br><br>You don\'t need to download it, you can see information about the hotel, get recommendations of places to visit and even chat with reception!<br><br>Excellent companion to enjoy our stay to the fullest.',
                'fr'=>"Bonjour [nombre],<br>Avez-vous essayé la WebApp de [nombre_del_hotel]?<br><br>Vous n'avez pas besoin de le télécharger, vous pouvez voir des informations sur l'hôtel, obtenir des recommandations de lieux à visiter et même discuter avec la réception!<br><br>Excellent compagnon pour profiter pleinement de notre séjour."
            ];
            //
            $settings->chat_hoster = [
                'when_msg_received_guest'=>[
                    'via_platform'=>true,
                    'via_email'=>false,
                ],
                'when_not_answered'=>[
                    'via_platform'=>true,
                    'via_email'=>true,
                ],
            ];
            $settings->chat_guest = [
                'when_unread_message'=>[
                    'via_sms'=>true,
                    'via_email'=>false,
                ],
            ];
            return $settings;
            //
            //no se usa por ahora
            // 'arrival_check_email' => true,//NO USANDO
            // 'arrival_msg_email' => '', //NO USANDO
            // 'arrival_lang_email' => 'es', //NO USANDO
            // 'arrival_check_sms' => false, //NO USANDO
            // 'arrival_msg_sms' => '', //NO USANDO
            // 'arrival_lang_sms' => 'es', //NO USANDO
            // 'preout_check_email' => true, //NO USANDO
            // 'preout_msg_email' => '', //NO USANDO
            // 'preout_lang_email' => 'es', //NO USANDO
            // 'preout_check_sms' => false, //NO USANDO
            // 'preout_msg_sms' => '', //NO USANDO
            // 'preout_lang_sms' => 'es', //NO USANDO
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

if (! function_exists('formatTimeDifference')) {
    function formatTimeDifference($date)
    {
        $timeDiff = $date;

        if ($timeDiff->diffInYears() > 0) {
            return $timeDiff->diffInYears() . ' ' . Str::plural('año', $timeDiff->diffInYears());
        } elseif ($timeDiff->diffInMonths() > 0) {
            return $timeDiff->diffInMonths() . ' ' . Str::plural('mes', $timeDiff->diffInMonths());
        } elseif ($timeDiff->diffInDays() > 0) {
            return $timeDiff->diffInDays() . ' ' . Str::plural('día', $timeDiff->diffInDays());
        } elseif ($timeDiff->diffInHours() > 0) {
            return $timeDiff->diffInHours() . ' ' . Str::plural('hora', $timeDiff->diffInHours());
        } elseif ($timeDiff->diffInMinutes() > 0) {
            return $timeDiff->diffInMinutes() . ' ' . Str::plural('minuto', $timeDiff->diffInMinutes());
        } else {
            return $timeDiff->diffInSeconds() . ' ' . Str::plural('segundo', $timeDiff->diffInSeconds());
        }
    }

}



//preparelink
if (! function_exists('prepareLink')) {
    function prepareLink($data,$hotel,$params_url = null){
        $link = url('webapp?e='.$data['stay_id'].'&g='.$data['guest_id'].'&lang='.$data['stay_lang']);
        $link =  includeSubdomainInUrlHuesped($link, $hotel);
        if($params_url){
            $link = $link.$params_url;
        }
        return $link;
    }
}

if (! function_exists('sendSMS')) {
    function sendSMS($phone,$msg,$from){
            //reducir nombre_del_hotel
        if(config('services.smsapi.active')){
            $hotelName = $from;
            $words = explode(' ', $hotelName);

            $alias = $words[0];
            array_shift($words); // Eliminamos la primera palabra

            $currentLength = strlen($alias);

            foreach ($words as $word) {
                if (strlen($word) < 3) {
                    continue; // Saltamos palabras de menos de 3 caracteres
                }

                // Calculamos espacio restante considerando el espacio entre palabras
                $remainingSpace = 11 - $currentLength - 1;

                if ($remainingSpace >= strlen($word)) {
                    // Si la palabra cabe completa
                    $alias .= " " . $word;
                    $currentLength += strlen($word) + 1; // +1 para el espacio
                } elseif ($remainingSpace > 1) {
                    // Si la palabra no cabe completa pero hay espacio para parte de ella y un punto
                    $alias .= " " . substr($word, 0, $remainingSpace - 1) . ".";
                    break; // Terminamos el proceso porque ya no hay más espacio para añadir palabras o caracteres
                } else {
                    break; // Terminamos el proceso porque no hay espacio suficiente para añadir más palabras
                }
            }
            //
            //


            $response = Http::withHeaders([
                'X-Api-Key' => config('services.smsapi.token'),
                'Accept' => 'application/json', // Añade esto
            ])->post('https://rest.smsmode.com/sms/v1/messages', [
                'recipient' => [ 'to' => $phone],
                'body' => [ 'text' => $msg],
                'from' => $alias,
            ]);
            return $response;
        }else{
            return 'SMS inactivo';
        }
    }
}

if (! function_exists('includeSubdomainInUrlHuesped')) {
    function includeSubdomainInUrlHuesped($url, $hotel){
        if (!$url || !$hotel) return;
        $production  = config('app.env');
        $url_base_huesped = $url;
        if ($production == 'test' || $production == 'pro') {
            $resultURL = str_replace('api', $hotel['subdomain'], $url_base_huesped);
            return $resultURL;
        }
        $guest_path  = config('app.guest_path');
        $request = Request::create($url_base_huesped);
        $updated_url = $request->fullUrlWithQuery(['subdomain' => $hotel['subdomain']]);
        $resultURL = str_replace(url(''), $guest_path, $updated_url);
        return $resultURL;
    }
}

if (! function_exists('buildUrlWebApp')) {
    function buildUrlWebApp($chainSubdomain, $hotelSlug = null, $uri = null, $paramsString = null){
        $resultURL = null;
        $guest_path  = config('app.guest_path');
        $env  = config('app.env');
        if($env == "local"){
            $hotelSlug ? $guest_path .= "/$hotelSlug": '';
            $uri ? $guest_path .= "/$uri": '';
            $guest_path .= "?chainsubdomain={$chainSubdomain}&subdomain={$hotelSlug}";
            $paramsString ? $guest_path .= "&{$paramsString}" : '';
            $resultURL = $guest_path;
        }else{
            $urlBase = url('/');
            $resultURL = str_replace('api', $chainSubdomain, $urlBase);
            $hotelSlug ? $resultURL .= "/$hotelSlug": '';
            $uri ? $resultURL .= "/$uri": '';
            $paramsString ? $resultURL .= "?{$paramsString}" : '';
        }
        return $resultURL;
    }
}

if (! function_exists('sendEventPusher')) {
    function sendEventPusher($channel,$event,$data){
        $pusher = new Pusher(
            config('services.pusher.key'),
            config('services.pusher.secret'),
            config('services.pusher.id'),
            ['cluster' => config('services.pusher.cluster')]
        );
        $pusher->trigger($channel, $event, $data);
    }
}

if (!function_exists('defaultChatSettings')) {
    function defaultChatSettings() {
        $chat_settings = new stdClass();
        $chat_settings->name = 'Chat';
        $chat_settings->show_guest = true;
        $chat_settings->languages = [
            Language::where('abbreviation', 'es')->first(),
            Language::where('abbreviation', 'en')->first(),
        ];
        $chat_settings->first_available_msg = [
            "es" => "Hola. Un miembro del personal atenderá tu consulta lo antes posible.",
            "en" => "Hello. A member of staff will attend to your query as soon as possible.",
            "fr" => "Salut. Un membre du personnel répondra à votre demande dans les plus brefs délais.",
        ];
        $chat_settings->first_available_show = true;
        $chat_settings->not_available_msg = [
            "es" => "Ahora mismo no contamos con personal disponible. Puedes consultar nuestro horario de disponibilidad en la barra del chat.",
            "en" => "Right now we do not have staff available. You can check our availability hours in the chat bar.",
            "fr" => "Pour le moment, nous n'avons pas de personnel disponible. Vous pouvez vérifier nos heures de disponibilité dans la barre de discussion.",
        ];
        $chat_settings->not_available_show = true;
        $chat_settings->second_available_msg = [
            "es" => "Perdona la tardanza, nuestro personal está ocupado ahora mismo. Intentaremos atender tu consulta cuando haya personal libre.",
            "en" => "Sorry for the delay, our staff is busy right now. We will try to answer your question when there are free staff.",
            "fr" => "Désolé pour le retard, notre personnel est occupé en ce moment. Nous essaierons de répondre à votre question lorsqu'il y aura du personnel libre.",
        ];
        $chat_settings->second_available_show = true;
        $chat_settings->three_available_msg = [
            "es" => "Parece que está tardando más de lo esperado, disculpa las molestias. Podrías dejarnos lo que necesitas y te responderemos lo antes posible. También te avisaremos de la respuesta por mail.",
            "en" => "Seems to be taking longer than expected, sorry for the inconvenience. You could leave us what you need and we will reply to you as soon as possible. We will also notify you of the response by email.",
            "fr" => "Cela semble prendre plus de temps que prévu, désolé pour le désagrément. Vous pouvez nous laisser ce dont vous avez besoin et nous vous répondrons dans les plus brefs délais. Nous vous informerons également de la réponse par e-mail.",
        ];
        $chat_settings->three_available_show = true;
        $chat_settings->email_notify_new_message_to = [];
        $chat_settings->email_notify_pending_chat_to = ['Operator'];
        $chat_settings->email_notify_not_answered_chat_to = ['Associate','Administrator','Operator'];

        return $chat_settings;
    }
}

if (! function_exists('defaultChatHours')) {
    function defaultChatHours(){
        $chat_hours =[
            ['day' => 'Lunes', 'active' => true, 'horary' => [['start'=>'00:00','end'=>'23:59']]],
            ['day' => 'Martes', 'active' => true, 'horary' => [['start'=>'00:00','end'=>'23:59']]],
            ['day' => 'Miércoles', 'active' => true, 'horary' => [['start'=>'00:00','end'=>'23:59']]],
            ['day' => 'Jueves', 'active' => true, 'horary' => [['start'=>'00:00','end'=>'23:59']]],
            ['day' => 'Viernes', 'active' => true, 'horary' => [['start'=>'00:00','end'=>'23:59']]],
            ['day' => 'Sábado', 'active' => true, 'horary' => [['start'=>'00:00','end'=>'23:59']]],
            ['day' => 'Domingo', 'active' => true, 'horary' => [['start'=>'00:00','end'=>'23:59']]],
        ];
        return $chat_hours;
    }
}

if (! function_exists('preStayqueriesTextDefault')) {
    function preStayqueriesTextDefault(){
        $queriesTextDefault = new stdClass();
        $queriesTextDefault->pre_stay_activate = true;
        $queriesTextDefault->pre_stay_thanks = [
            "es" => "Agradecemos sinceramente tu feedback. Nos importa tu experiencia y tratamos de cumplir tus expectativas.",
            "en" => "We sincerely appreciate your feedback. We care about your experience and we try to meet your expectations.",
            "fr" => "Nous apprécions sincèrement vos commentaires. Nous nous soucions de votre expérience et nous essayons de répondre à vos attentes.",
            "pt" => "Agradecemos sinceramente o seu feedback. Preocupamo-nos com a sua experiência e tentamos corresponder às suas expectativas.",
            "it" => "Apprezziamo sinceramente il tuo feedback. Abbiamo a cuore la tua esperienza e cerchiamo di soddisfare le tue aspettative.",
            "de" => "Wir freuen uns sehr über Ihr Feedback. Ihre Erfahrung liegt uns am Herzen und wir versuchen, Ihre Erwartungen zu erfüllen."
        ];
        $queriesTextDefault->pre_stay_comment = [
            "es" => "Nos encantaría saber los detalles, nos importa tu experiencia.",
            "en" => "We would love to know the details, we care about your experience.",
            "fr" => "Nous aimerions connaître les détails, nous nous soucions de votre expérience.",
            "pt" => "Adoraríamos saber os detalhes, preocupamo-nos com a sua experiência.",
            "it" => "Ci piacerebbe conoscere i dettagli, abbiamo a cuore la tua esperienza.",
            "de" => "Wir würden gerne die Details erfahren, uns liegt Ihr Erlebnis am Herzen."
        ];
        return $queriesTextDefault;
    }
}

if (! function_exists('inStayqueriesTextDefault')) {
    function inStayqueriesTextDefault(){
        $queriesTextDefault = new stdClass();
        $queriesTextDefault->in_stay_activate = true;
        $queriesTextDefault->in_stay_thanks_good = [
            "es" => "Nos alegra mucho saber que estás disfrutando tu estancia. Agradecemos sinceramente tu feedback y esperamos seguir cumpliendo tus expectativas.",
            "en" => "We are very happy to know that you are enjoying your stay. We sincerely appreciate your feedback and hope to continue meeting your expectations.",
            "fr" => "Nous sommes très heureux de savoir que vous appréciez votre séjour. Nous apprécions sincèrement vos commentaires et espérons continuer à répondre à vos attentes.",
            "pt" => "Estamos muito felizes por saber que está a desfrutar da sua estadia. Agradecemos sinceramente o seu feedback e esperamos continuar a corresponder às suas expectativas.",
            "it" => "Siamo molto felici di sapere che ti stai godendo il tuo soggiorno. Apprezziamo sinceramente il tuo feedback e speriamo di continuare a soddisfare le tue aspettative.",
            "de" => "Wir freuen uns sehr, dass Sie Ihren Aufenthalt genießen. Wir freuen uns sehr über Ihr Feedback und hoffen, Ihre Erwartungen weiterhin erfüllen zu können."
        ];
        $queriesTextDefault->in_stay_thanks_normal = [
            "es" => "Lamentamos que tu estancia no esté siendo la ideal. Nos gustaría saber más para tratar de mejorarla.",
            "en" => "We are sorry that your stay was not ideal. We would like to know more to try to improve it.",
            "fr" => "Nous sommes désolés que votre séjour n'ait pas été idéal. Nous aimerions en savoir plus pour essayer de l'améliorer.",
            "pt" => "Lamentamos que a sua estadia não tenha sido a ideal. Gostaríamos de saber mais para tentar melhorá-lo.",
            "it" => "Ci dispiace che il tuo soggiorno non sia stato l'ideale. Vorremmo saperne di più per provare a migliorarlo.",
            "de" => "Es tut uns leid, dass Ihr Aufenthalt nicht optimal war. Wir würden gerne mehr wissen, um zu versuchen, es zu verbessern."
        ];
        $queriesTextDefault->in_stay_comment = [
            "es" => "Nos encantaría saber más detalles, buscamos mejorar tu experiencia.",
            "en" => "We would love to know more details, we seek to improve your experience.",
            "fr" => "Nous aimerions connaître plus de détails, nous cherchons à améliorer votre expérience.",
            "pt" => "Adoraríamos saber mais detalhes, procuramos melhorar a sua experiência.",
            "it" => "Ci piacerebbe conoscere maggiori dettagli, cerchiamo di migliorare la tua esperienza.",
            "de" => "Wir würden gerne mehr Details erfahren, wir möchten Ihr Erlebnis verbessern."
        ];
        return $queriesTextDefault;
    }
}

if (! function_exists('postStayqueriesTextDefault')) {
    function postStayqueriesTextDefault(){
        $queriesTextDefault = new stdClass();
        $queriesTextDefault->post_stay_thanks_good = [
            "es" => "¡Nos alegra que hayas disfrutado en nuestro hotel! Agradecemos sinceramente tu feedback y esperamos que vuelvas a disfrutar pronto una estancia con nosotros.",
            "en" => "We are glad that you enjoyed your stay at our hotel! We sincerely appreciate your feedback and hope that you enjoy a stay with us again soon.",
            "fr" => "Nous sommes heureux que vous ayez apprécié votre séjour dans notre hôtel ! Nous apprécions sincèrement vos commentaires et espérons que vous apprécierez à nouveau un séjour parmi nous bientôt.",
            "pt" => "Estamos felizes que tenha gostado da sua estadia no nosso hotel! Agradecemos sinceramente o seu feedback e esperamos que desfrute de uma estadia connosco novamente em breve.",
            "it" => "Siamo lieti che ti sia piaciuto il tuo soggiorno presso il nostro hotel! Apprezziamo sinceramente il tuo feedback e speriamo che ti piaccia di nuovo un soggiorno con noi presto.",
            "de" => "Wir freuen uns, dass Sie Ihren Aufenthalt in unserem Hotel genossen haben! Wir freuen uns sehr über Ihr Feedback und hoffen, dass Sie bald wieder einen angenehmen Aufenthalt bei uns genießen."
        ];
        $queriesTextDefault->post_stay_thanks_normal = [
            "es" => "Lamentamos que tu estancia no haya sido perfecta. Nos ayudaría conocer tu opinión para entender la situación, es muy importante para nosotros.",
            "en" => "We are sorry that your stay was not perfect. It would help us to know your opinion to understand the situation, it is very important for us",
            "fr" => "Nous sommes désolés que votre séjour n'ait pas été parfait. Cela nous aiderait à connaître votre avis pour comprendre la situation, c'est très important pour nous",
            "pt" => "Lamentamos que a sua estadia não tenha sido perfeita. Ajudar-nos-ia saber a sua opinião para compreender a situação, é muito importante para nós.",
            "it" => "Ci dispiace che il tuo soggiorno non sia stato perfetto. Ci aiuterebbe conoscere la tua opinione per capire la situazione, per noi è molto importante.",
            "de" => "Es tut uns leid, dass Ihr Aufenthalt nicht perfekt war. Es würde uns helfen, Ihre Meinung zu erfahren, um die Situation zu verstehen. Das ist für uns sehr wichtig."
        ];
        $queriesTextDefault->post_stay_comment = [
            "es" => "Nos encantaría saber más detalles, buscamos mejorar tu experiencia.",
            "en" => "We would love to know more details, we seek to improve your experience.",
            "fr" => "Nous aimerions connaître plus de détails, nous cherchons à améliorer votre expérience.",
            "pt" => "Adoraríamos saber mais detalhes, procuramos melhorar a sua experiência.",
            "it" => "Ci piacerebbe conoscere maggiori dettagli, cerchiamo di migliorare la tua esperienza.",
            "de" => "Wir würden gerne mehr Details erfahren, wir möchten Ihr Erlebnis verbessern."
        ];
        return $queriesTextDefault;
    }
}

if (! function_exists('queryNotifyDefault')) {
    function queryNotifyDefault(){
        $queriesNotifyDefault = new stdClass();
        $queriesNotifyDefault->notify_to_hoster = [
            "notify_when_guest_send_via_platform" => true,
            "notify_when_guest_send_via_email" => false,
            "notify_later_when_guest_send_via_platform" => true,
            "notify_later_when_guest_send_via_email" => false,
        ];//borrar en algun momento luego de la transicion
        $queriesNotifyDefault->email_notify_new_feedback_to = [];
        $queriesNotifyDefault->email_notify_pending_feedback_to = ['Operator'];
        return $queriesNotifyDefault;
    }
}

if (! function_exists('queriesTextDefault')) {
    function queriesTextDefault(){
        $queriesTexts1 = preStayqueriesTextDefault();
        $queriesTexts2 = inStayqueriesTextDefault();
        $queriesTexts3 = postStayqueriesTextDefault();
        $queriesSettingsNotify = queryNotifyDefault();

        // Convertimos los objetos a arrays
        $array1 = get_object_vars($queriesTexts1);
        $array2 = get_object_vars($queriesTexts2);
        $array3 = get_object_vars($queriesTexts3);
        $array4 = get_object_vars($queriesSettingsNotify);

        // Fusionamos los arrays
        $mergedArray = array_merge($array1, $array2, $array3,$array4);

        // Convertimos el array resultante de nuevo a un objeto
        return (object)$mergedArray;
    }
}

if (! function_exists('requestSettingsDefault')) {
    function requestSettingsDefault(){
        $requestSettings = new stdClass();
        $requestSettings->msg_title = [
            "es" => "<p>¡Nos alegra que hayas disfrutado en [nombre del hotel]!</p>",
            "en" => "<p>We are glad you enjoyed your stay at [nombre del hotel]!</p>",
            "fr" => "<p>Nous sommes heureux que vous ayez apprécié votre séjour à [nombre del hotel]!</p>",
        ];
        $requestSettings->msg_text = [
            "es" => '<p>Tu experiencia es muy importante, compartirla ayudaría a otros viajeros a conocernos.</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p>Si reservaste online, podrían solicitarte tu opinión pronto. Valoramos mucho que la compartieras.</p><p><br></p><p class="ql-align-center"><strong>Agradecemos tu tiempo y ¡Gracias por habernos elegido!</strong></p>',
            "en" => '<p>Your experience is very important, sharing it would help other travelers get to know us.</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p>If you booked online, you may be asked for your opinion soon. We really appreciate that you shared it.</p><p><br></p><p class="ql-align-center"><strong>We appreciate your time and thank you for choosing us!</strong></p>',
            "fr" => "<p>Votre expérience est très importante, la partager aiderait d'autres voyageurs à nous connaître.</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p>Si vous avez réservé en ligne, votre avis pourrait bientôt vous être demandé. Nous apprécions vraiment que vous l'ayez partagé.</p><p><br></p><p class='ql-align-center'><strong>Nous apprécions votre temps et merci de nous avoir choisis !</strong></p>",
        ];
        $requestSettings->otas_enabled = [
            "google" => true,
            "tripadvisor" => true
        ];
        $requestSettings->request_to = json_encode(['GOOD','VERYGOOD']);
        return $requestSettings;
    }
}

// ===============================

if (! function_exists('saveImage')) {
    function saveImage($image, $model, $id, $type = null, $withname = false, $customname = null) {

        $storage_env = config('app.storage_env');
        $rand = mt_Rand(1000000, 9999999);
        $ext = '.'.$image->extension();
        $time = time();
        if ($customname) {
            $customname = $customname.$ext;
        }
        $name_file = generateImageName($image, $withname, $rand, $time);
        $savePath = getImageSavePath($model, $name_file);
        if($storage_env == "test" || $storage_env == "pro") {
            if ($ext == '.svg') {
                $content = file_get_contents($image->getRealPath());
                Storage::disk('s3')->put($savePath, $content);
            } else {
                $imgFileOriginal = resizeImage($image->getRealPath());
                saveImageToS3($imgFileOriginal, $savePath);
            }
        } else {
            if ($ext == '.svg'){
                $image->move(public_path('storage/'.$model.'/'), $name_file);
            } else {
                $imgFileOriginal = resizeImage($image->getRealPath());
                saveImageToFilesystem($imgFileOriginal, public_path($savePath));
            }
        }
        $savePath = '/'.$savePath;

        //guardar en bd
        switch ($model) {
            case 'hotel':
                $imageHotel = new ImagesHotels;
                $imageHotel->hotel_id = $id;
                $imageHotel->type = $type;
                $imageHotel->name = $name_file;
                $imageHotel->url = $savePath;
                $imageHotel->save();
                break;
            case 'gallery':
                $m_gallery = new ImageGallery();
                $m_gallery->image_id = $id;
                $m_gallery->concept = $type;
                $m_gallery->url = $savePath;
                $m_gallery->type = 'STORAGE';
                $m_gallery->name = $customname ? $customname :  $name_file;
                $m_gallery->save();
                break;
            default:
                # code...
                break;
        }

        return $savePath;
    }
}

//IMAGE METHODS
function generateImageName($image, $withname, $rand, $time) {
    $ext = '.'.$image->extension();
    if ($withname) {
        return $time.'-'.$rand.'-'.$image->getClientOriginalName();
    } else {
        return $time.'-'.$rand.$ext;
    }
}
function resizeImage($imagePath, $width = 1200) {
    $imgFileOriginal = ImageManager::make($imagePath);
    $currentWidth = $imgFileOriginal->width();
    if ($currentWidth > $width) {
        $imgFileOriginal->resize($width, null, function($constraint){
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }
    return $imgFileOriginal;
}
function saveImageToFilesystem($imgFile, $path, $quality = 72) {
    $imgFile->save($path, $quality);
}
function saveImageToS3($imgFile, $path) {
    $imgFile->stream(); // Prepara la imagen para ser guardada en el stream
    Storage::disk('s3')->put($path, $imgFile->__toString(), 'public');
}
function getImageSavePath($model, $name_file) {
    return 'storage/'.$model.'/'.$name_file;
}
