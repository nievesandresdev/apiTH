<?php

use App\Models\Language;
use App\Utils\Enums\EnumResponse;
use App\Utils\Enums\InventoryError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Pusher\Pusher;

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

if (! function_exists('getAllLanguages')) {
    function getAllLanguages(){
        $otas =  collect(['es', 'en', 'fr']);
        return $otas;
    }
}

if (! function_exists('settingsNotyStayDefault')) {
    function settingsNotyStayDefault(){
        return [
            'unfilled_check_platform' => true,
            'unfilled_check_email' => true,
            //
            'create_check_email' => true,
            //Mensaje al añadir un envío a un huésped al crear estancia
            'create_msg_email' => [
                    /* 'es'=>'<p>¡Hola [nombre]!<br><br>¡Bienvenido al [nombre_del_hotel]. Queremos que disfrutes al máximo tu estancia, por eso te invitamos a explorar nuestra webapp exclusiva. Accede a [URL] y compártela con el resto de huéspedes de la estancia para conocer toda la información del hotel y una guía completa de la ciudad. Si necesitas ayuda, nuestro equipo está disponible. ¡Esperamos que tengas una experiencia increíble en nuestro hotel! <br><br>El equipo del [nombre_del_hotel].<p>',
                    'en'=>'<p>Hello [nombre]!<br><br>Welcome to [nombre_del_hotel]. We want you to enjoy your stay to the fullest, which is why we invite you to explore our exclusive webapp. Access [URL] and share it with the rest of the guests of the stay to find out all the hotel information and a complete guide to the city. If you need help, our team is available. We hope you have an amazing experience at our hotel! <br><br>The [nombre_del_hotel] team.<p>',
                    'fr'=>"<p>Bonjour [nombre] !<br><br>Bienvenue à [nombre_del_hotel]. Nous souhaitons que vous profitiez au maximum de votre séjour, c'est pourquoi nous vous invitons à explorer notre webapp exclusive. Accédez à [URL] et partagez-le avec le reste des invités du séjour pour découvrir toutes les informations de l'hôtel et un guide complet de la ville. Si vous avez besoin d'aide, notre équipe est disponible. Nous espérons que vous vivrez une expérience incroyable dans notre hôtel ! <br><br>L'équipe de [nombre_del_hotel].<p>" */
                    'es' => 'Hola [nombre],<br>Nos alegra que utilices nuestra WebApp!<br><br>Aprovecha al máximo todo lo que te ofrecemos:<br>Conoce las instalaciones del hotel<br>Descubre sitios turísticos imperdibles<br>Disfruta con nuestras recomendaciones de restaurantes<br>Comunícate con nuestro personal utilizando el Chat<br><br>Estamos a tu disposición para brindarte la mejor experiencia en tu estancia. Gracias por elegirnos.',
                    'en' => 'Hello [nombre],<br>We are glad you are using our WebApp!<br><br>Take full advantage of everything we offer you:<br>Get to know the hotel facilities<br>Discover unmissable tourist sites<br>Enjoy our restaurant recommendations<br>Communicate with our staff using the Chat<br><br>We are at your disposal to provide you with the best experience during your stay. Thank you for choosing us.',
                    'fr' => 'Bonjour [nombre],<br>Nous sommes ravis que vous utilisiez notre WebApp!<br><br>Profitez pleinement de tout ce que nous vous offrons:<br>Découvrez les installations de l\'hôtel<br>Découvrez des sites touristiques incontournables<br>Profitez de nos recommandations de restaurants<br>Communiquez avec notre personnel en utilisant le Chat<br><br>Nous sommes à votre disposition pour vous offrir la meilleure expérience lors de votre séjour. Merci de nous avoir choisis.'
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
            //Mensaje cuando un huésped crea una estancia
            /* 'guestcreate_msg_email' => [
                'es'=>'<p>¡Hola [nombre]!<br><br>¡Esperamos que  disfrutes tu estancia en el [nombre_del_hotel]! Te invitamos a compartir la webapp con el resto de huéspedes [URL]. Descubrirán detalles del hotel y una guía completa de la ciudad. ¡Estamos aquí para que disfrutes al máximo! <br><br>El equipo del [nombre_del_hotel].<p>',
                'en'=>'<p>Hello [nombre]!<br><br>We hope you enjoy your stay at [nombre_del_hotel]! We invite you to share the webapp with the rest of the guests [URL]. You will discover details of the hotel and a complete guide to the city. We are here for you to enjoy to the fullest! <br><br>The [nombre_del_hotel] team.<p>',
                'fr'=>"<p>Bonjour [nombre]!<br><br>Nous espérons que vous apprécierez votre séjour à [nombre_del_hotel]! Nous vous invitons à partager la webapp avec le reste des invités [URL]. Vous découvrirez les détails de l'hôtel et un guide complet de la ville. Nous sommes là pour que vous en profitiez au maximum ! <br><br>L'équipe de [nombre_del_hotel].<p>"
            ], */
            'guestcreate_msg_email' => [

                'es' => 'Estimado huésped,<br> Estamos encantados de darte la bienvenida a [nombre_del_hotel].<br><br>Esperamos que tu estancia sea única e inolvidable,<br>nuestro equipo está a tu disposición.',

                'en'=>'<p>Dear guest,<br> We are delighted to welcome you to [nombre_del_hotel].<br> We hope your stay is unique and unforgettable.<br> Our team is at your service.<p>',

                'fr'=>"Cher invité,<br> Nous sommes ravis de vous accueillir à [nombre_del_hotel].<br> Nous espérons que votre séjour sera unique et inoubliable.<br> Notre équipe est à votre disposition."
            ],
            //
            'guestinvite_check_email' => true,
            //Mensaje cuando un huésped invita a otro huésped cuando ya esta la estancia creada
            /* 'guestinvite_msg_email' => [
                'es'=>'<p>¡Hola [nombre]!<br><br>Échale un vistazo a la webapp de [nombre_del_hotel], que está llena de información para hacer nuestra experiencia aún más completa. Accede a través de [URL]. Descubre detalles del hotel y una guía completa de la ciudad. ¡Disfrutarás al máximo!<p>',
                'en'=>'<p>Hello [nombre]!<br><br>Take a look at the [nombre_del_hotel] webapp, which is full of information to make our experience even more complete. Access through [URL]. Discover hotel details and a complete city guide. You will enjoy it to the fullest!<p>',
                'fr'=>"<p>Bonjour [nombre]!<br><br>Jetez un œil à la webapp [nombre_del_hotel], qui regorge d'informations pour rendre notre expérience encore plus complète. Accès via [URL]. Découvrez les détails de l'hôtel et un guide complet de la ville. Vous en profiterez pleinement!<p>"
            ], */
            'guestinvite_msg_email' => [
                'es' => 'Hola [nombre],<br>¿Ya has probado la WebApp de [nombre_del_hotel]?<br><br>No necesitas descargarla, puedes ver información del hotel, obtener recomendaciones de lugares para visitar y hasta chatear con recepción!<br><br>Excelente acompañante para disfrutar al máximo nuestra estancia.',

                'en'=>'Hello [nombre],<br>Have you tried the WebApp of [nombre_del_hotel]?<br><br>You don\'t need to download it, you can see information about the hotel, get recommendations of places to visit and even chat with reception!<br><br>Excellent companion to enjoy our stay to the fullest.',

                'fr'=>"Bonjour [nombre],<br>Avez-vous essayé la WebApp de [nombre_del_hotel]?<br><br>Vous n'avez pas besoin de le télécharger, vous pouvez voir des informations sur l'hôtel, obtenir des recommandations de lieux à visiter et même discuter avec la réception!<br><br>Excellent compagnon pour profiter pleinement de notre séjour."
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
        ];
        $queriesTextDefault->pre_stay_comment = [
            "es" => "Nos encantaría saber los detalles, nos importa tu experiencia.",
            "en" => "We would love to know the details, we care about your experience.",
            "fr" => "Nous aimerions connaître les détails, nous nous soucions de votre expérience.",
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
        ];
        $queriesTextDefault->in_stay_thanks_normal = [
            "es" => "Lamentamos que tu estancia no esté siendo la ideal. Nos gustaría saber más para tratar de mejorarla.",
            "en" => "We are sorry that your stay was not ideal. We would like to know more to try to improve it.",
            "fr" => "Nous sommes désolés que votre séjour n'ait pas été idéal. Nous aimerions en savoir plus pour essayer de l'améliorer.",
        ];
        $queriesTextDefault->in_stay_comment = [
            "es" => "Nos encantaría saber más detalles, buscamos mejorar tu experiencia.",
            "en" => "We would love to know more details, we seek to improve your experience.",
            "fr" => "Nous aimerions connaître plus de détails, nous cherchons à améliorer votre expérience.",
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
        ];
        $queriesTextDefault->post_stay_thanks_normal = [
            "es" => "Lamentamos que tu estancia no haya sido perfecta. Nos ayudaría conocer tu opinión para entender la situación, es muy importante para nosotros.",
            "en" => "We are sorry that your stay was not perfect. It would help us to know your opinion to understand the situation, it is very important for us",
            "fr" => "Nous sommes désolés que votre séjour n'ait pas été parfait. Cela nous aiderait à connaître votre avis pour comprendre la situation, c'est très important pour nous",
        ];
        $queriesTextDefault->post_stay_comment = [
            "es" => "Nos encantaría saber más detalles, buscamos mejorar tu experiencia.",
            "en" => "We would love to know more details, we seek to improve your experience.",
            "fr" => "Nous aimerions connaître plus de détails, nous cherchons à améliorer votre expérience.",
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
        ];
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
            "es" => "¡Nos alegra que hayas disfrutado en [nombre del hotel]!",
            "en" => "We are glad you enjoyed your stay at [hotel name]!",
            "fr" => "Nous sommes heureux que vous ayez apprécié votre séjour à [nom de l'hôtel]!",
        ];
        $requestSettings->msg_text = [
            "es" => '<p>Tu experiencia es muy importante, compartirla ayudaría a otros viajeros a conocernos.</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p>Si reservaste online, podrían solicitarte tu opinión pronto. Valoramos mucho que la compartieras.</p><p><br></p><p class="ql-align-center"><strong>Agradecemos tu tiempo y ¡Gracias por habernos elegido!</strong></p>',
            "en" => '<p>Your experience is very important, sharing it would help other travelers get to know us.</p><p><br></p><p><strong>[Link to OTAs]</strong></p><p><br></p><p>If you booked online, you may be asked for your opinion soon. We really appreciate that you shared it.</p><p><br></p><p class="ql-align-center"><strong>We appreciate your time and thank you for choosing us!</strong></p>',
            "fr" => "<p>Votre expérience est très importante, la partager aiderait d'autres voyageurs à nous connaître.</p><p><br></p><p><strong>[Lien vers les OTA]</strong></p><p><br></p><p>Si vous avez réservé en ligne, votre avis pourrait bientôt vous être demandé. Nous apprécions vraiment que vous l'ayez partagé.</p><p><br></p><p class='ql-align-center'><strong>Nous apprécions votre temps et merci de nous avoir choisis !</strong></p>",
        ];
        $requestSettings->otas_enabled = [
            "google" => true,
            "tripadvisor" => true
        ];
        $requestSettings->request_to = "positive queries";
        return $requestSettings;
    }
}