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
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

use App\Services\Notification\DiscordService;

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

if (!function_exists('getDefaultHotelCommunications')) {
    function getDefaultHotelCommunications()
    {
        return [
            'email' => [
                'welcome_email' => true,
                'pre_checkin_email' => true,
                'post_checkin_email' => true,
                'checkout_email' => true,
                'pre_checkout_email' => true,
                'new_chat_email' => true,
                'referent_email' => true,
            ],
            // se pueden agregar más tipos en el futuro
            // 'ejemplo' => [
            //     'welcome' => false,
            //     'pre_checkin' => false,
            //     ...
            // ],
        ];
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
        // $chainSubdomain = $chain->subdomain;
        // $isIndependentChain = $chain->type == "INDEPENDENT";
        $resultURL = null;
        $guest_path  = config('app.guest_path');
        $env  = config('app.env');
        if($env == "local"){
            $hotelSlug ? $guest_path .= "/$hotelSlug": '';
            $uri ? $guest_path .= "/$uri": '';
            $guest_path .= "?chainsubdomain={$chainSubdomain}";
            $paramsString ? $guest_path .= "&{$paramsString}" : '';
            $resultURL = $guest_path;
        }else{
            $urlBase = url('/');
            $resultURL = str_replace('api', $chainSubdomain, $urlBase);
            $hotelSlug ? $resultURL .= "/$hotelSlug": '';
            $uri ? $resultURL .= "/$uri": '';
            $paramsString ? $resultURL .= "?{$paramsString}" : '';
            $resultURL = str_replace('.io', '.app', $resultURL); // sustituir .io por .app para el sprint #4
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
            "pt" => "Olá. Um membro da equipe atenderá sua consulta o mais rápido possível.",
            "it" => "Ciao. Un membro del personale risponderà alla tua richiesta il prima possibile.",
            "de" => "Hallo. Ein Mitarbeiter wird Ihre Anfrage so schnell wie möglich bearbeiten.",
            "ca" => "Hola. Un membre del personal atendrà la teva consulta tan aviat com sigui possible.",
            "eu" => "Kaixo. Langile batek zure galdera ahal bezain laster erantzungo dio.",
            "gl" => "Ola. Un membro do persoal atenderá a túa consulta canto antes sexa posible.",
            "nl" => "Hallo. Een medewerker zal je vraag zo snel mogelijk behandelen."
        ];
        $chat_settings->first_available_show = true;
        $chat_settings->not_available_msg = [
            "es" => "Ahora mismo no contamos con personal disponible. Puedes consultar nuestro horario de disponibilidad en la barra del chat.",
            "en" => "Right now we do not have staff available. You can check our availability hours in the chat bar.",
            "fr" => "Pour le moment, nous n'avons pas de personnel disponible. Vous pouvez vérifier nos heures de disponibilité dans la barre de discussion.",
            "pt" => "No momento, não temos pessoal disponível. Você pode verificar nosso horário de disponibilidade na barra do chat.",
            "it" => "Al momento non abbiamo personale disponibile. Puoi controllare il nostro orario di disponibilità nella barra della chat.",
            "de" => "Zurzeit ist kein Personal verfügbar. Sie können unsere Verfügbarkeitszeiten in der Chatleiste einsehen.",
            "ca" => "En aquest moment no comptem amb personal disponible. Pots consultar el nostre horari de disponibilitat a la barra del xat.",
            "eu" => "Une honetan ez dugu pertsonal erabilgarri. Txat barran gure erabilgarritasun orduak kontsultatu ditzakezu.",
            "gl" => "Agora mesmo non contamos con persoal dispoñible. Podes consultar o noso horario de dispoñibilidade na barra do chat.",
            "nl" => "Op dit moment hebben we geen personeel beschikbaar. Je kunt onze beschikbaarheidsuren in de chatbalk bekijken."
        ];
        $chat_settings->not_available_show = true;
        $chat_settings->second_available_msg = [
            "es" => "Perdona la tardanza, nuestro personal está ocupado ahora mismo. Intentaremos atender tu consulta cuando haya personal libre.",
            "en" => "Sorry for the delay, our staff is busy right now. We will try to answer your question when there are free staff.",
            "fr" => "Désolé pour le retard, notre personnel est occupé en ce moment. Nous essaierons de répondre à votre question lorsqu'il y aura du personnel libre.",
            "pt" => "Desculpe a demora, nossa equipe está ocupada no momento. Tentaremos responder à sua pergunta assim que houver pessoal disponível.",
            "it" => "Mi scuso per il ritardo, il nostro personale è al momento occupato. Cercheremo di rispondere alla tua domanda non appena sarà disponibile del personale libero.",
            "de" => "Entschuldigung für die Verzögerung, unser Personal ist momentan beschäftigt. Wir werden versuchen, Ihre Frage zu beantworten, sobald Personal verfügbar ist.",
            "ca" => "Perdona la tardança, el nostre personal està ocupat ara mateix. Intentarem atendre la teva consulta quan hi hagi personal lliure.",
            "eu" => "Barkatu berandu egoteagatik, gure langileak une honetan lanpetuta daude. Saiatuko gara zure galdera erantzuten langile libreak egon direnean.",
            "gl" => "Desculpa a tardanza, o noso persoal está ocupado no momento. Tentaremos atender a túa consulta cando haxa persoal libre.",
            "nl" => "Sorry voor de vertraging, ons personeel is op dit moment druk bezig. We zullen proberen je vraag te beantwoorden zodra er personeel beschikbaar is."
        ];
        $chat_settings->second_available_show = true;
        $chat_settings->three_available_msg = [
            "es" => "Parece que está tardando más de lo esperado, disculpa las molestias. Podrías dejarnos lo que necesitas y te responderemos lo antes posible. También te avisaremos de la respuesta por mail.",
            "en" => "Seems to be taking longer than expected, sorry for the inconvenience. You could leave us what you need and we will reply to you as soon as possible. We will also notify you of the response by email.",
            "fr" => "Cela semble prendre plus de temps que prévu, désolé pour le désagrément. Vous pouvez nous laisser ce dont vous avez besoin et nous vous répondrons dans les plus brefs délais. Nous vous informerons également de la réponse par e-mail.",
            "pt" => "Parece que está demorando mais do que o esperado, pedimos desculpas pelo transtorno. Você pode nos deixar o que precisa e responderemos o mais rápido possível. Também enviaremos uma notificação por e-mail com a resposta.",
            "it" => "Sembra che ci stia volendo più tempo del previsto, ci scusiamo per il disagio. Puoi lasciarci il tuo messaggio e ti risponderemo il prima possibile. Ti informeremo anche della risposta via email.",
            "de" => "Es scheint länger zu dauern als erwartet, entschuldigen Sie bitte die Unannehmlichkeiten. Bitte hinterlassen Sie uns Ihre Anfrage, und wir werden Ihnen so schnell wie möglich antworten. Wir werden Sie auch per E-Mail über die Antwort informieren.",
            "ca" => "Sembla que està tardant més del previst, disculpa les molèsties. Podries deixar-nos el que necessites i et responrem tan aviat com sigui possible. També t'avisarem de la resposta per correu electrònic.",
            "eu" => "Dirudienez, espero baino gehiago denbora behar du, barkatu eragozpenak. Utzi mesedez behar duzun eta ahal bezain laster erantzungo dizugu. Halaber, posta elektronikoz jakinaratuko dizugu erantzuna.",
            "gl" => "Parece que está a demorar máis do esperado, desculpa as molestias. Poderías deixarnos o que necesitas e responderemos canto antes sexa posible. Tamén avisarémosche da resposta por correo electrónico.",
            "nl" => "Jouw ervaring is erg belangrijk, het delen ervan zou andere reizigers helpen ons te leren kennen. Wil je ons je recensie achterlaten?"
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
        $requestSettings->in_stay_activate = true;
        $requestSettings->in_stay_msg_title = [
            "es" => "<p>¡Muchas gracias [nombreHuesped]!</p>",
            "en" => "<p>Thank you very much [nombreHuesped]!</p>",
            "fr" => "<p>Merci beaucoup [nombreHuesped] !</p>",
            "pt" => "<p>Muito obrigado [nombreHuesped]!</p>",
            "it" => "<p>Grazie mille [nombreHuesped]!</p>",
            "de" => "<p>Vielen Dank [nombreHuesped]!</p>",
            "ca" => "<p>Moltes gràcies [nombreHuesped]!</p>",
            "eu" => "<p>Eskerrik asko [nombreHuesped]!</p>",
            "gl" => "<p>Moitas grazas [nombreHuesped]!</p>",
            "nl" => "<p>Hartelijk dank [nombreHuesped]!</p>"
        ];


        //
        $requestSettings->in_stay_msg_text = [
            "es" => '<p>Tu experiencia es muy importante, compartirla ayudaría a otros viajeros a conocernos. ¿Nos dejas tu reseña?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos tu tiempo y ¡Gracias por habernos elegido!</p>',
            "en" => '<p>Your experience is very important, sharing it would help other travelers get to know us. Would you leave us your review?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">We appreciate your time and thank you for choosing us!</p>',
            "fr" => "<p>Votre expérience est très importante, la partager aiderait d'autres voyageurs à nous connaître. Voulez-vous nous laisser votre avis ?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class='ql-align-center'>Nous apprécions votre temps et merci de nous avoir choisis !</p>",
            "pt" => "<p>Sua experiência é muito importante, compartilhá-la ajudaria outros viajantes a nos conhecerem. Você pode nos deixar sua avaliação?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class='ql-align-center'>Agradecemos seu tempo e obrigado por nos escolher!</p>",
            "it" => "<p>La tua esperienza è molto importante, condividerla aiuterebbe altri viaggiatori a conoscerci. Ci lasci la tua recensione?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class='ql-align-center'>Apprezziamo il tuo tempo e grazie per averci scelto!</p>",
            "de" => "<p>Ihre Erfahrung ist sehr wichtig, das Teilen würde anderen Reisenden helfen, uns kennenzulernen. Möchten Sie uns Ihre Bewertung hinterlassen?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class='ql-align-center'>Wir schätzen Ihre Zeit und danken Ihnen, dass Sie uns gewählt haben!</p>",
            "ca" => '<p>La teva experiència és molt important, compartir-la ajudaria altres viatgers a conèixer-nos. Ens deixes la teva ressenya?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">Agraïm el teu temps i gràcies per haver-nos triat!</p>',
            "eu" => '<p>Zure esperientzia oso garrantzitsua da, eta partekatzeak bidaiari beste batzuei gu ezagutzen lagunduko die. Utziko diguzu zure iritzia?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">Eskertzen dugu zure denbora eta mila esker gu aukeratzeagatik!</p>',
            "gl" => '<p>A túa experiencia é moi importante, compartila axudaría a outros viaxeiros a coñecernos. ¿Déixanos a túa opinión?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos o teu tempo e grazas por elixirnos!</p>',
            "nl" => '<p>Jouw ervaring is erg belangrijk, het delen ervan zou andere reizigers helpen ons te leren kennen. Wil je ons je recensie achterlaten?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">We waarderen je tijd en danken je dat je voor ons gekozen hebt!</p>'
        ];

        $requestSettings->in_stay_otas_enabled = [
            "booking" => false,
            "expedia" => false,
            "google" => true,
            "tripadvisor" => true,
            "airbnb" => false
        ];
        $requestSettings->msg_title = [
            "es" => "<p>¡Muchas gracias [nombreHuesped]!</p>",
            "en" => "<p>Thank you very much [nombreHuesped]!</p>",
            "fr" => "<p>Merci beaucoup [nombreHuesped] !</p>",
            "pt" => "<p>Muito obrigado [nombreHuesped]!</p>",
            "it" => "<p>Grazie mille [nombreHuesped]!</p>",
            "de" => "<p>Vielen Dank [nombreHuesped]!</p>",
            "ca" => "<p>Moltes gràcies [nombreHuesped]!</p>",
            "eu" => "<p>Eskerrik asko [nombreHuesped]!</p>",
            "gl" => "<p>Moitas grazas [nombreHuesped]!</p>",
            "nl" => "<p>Hartelijk dank [nombreHuesped]!</p>"
        ];

        $requestSettings->msg_text = [
            "es" => '<p>Tu experiencia es muy importante, compartirla ayudaría a otros viajeros a conocernos. ¿Nos dejas tu reseña?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos tu tiempo y ¡Gracias por habernos elegido!</p>',
            "en" => '<p>Your experience is very important, sharing it would help other travelers get to know us. Would you leave us your review?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">We appreciate your time and thank you for choosing us!</p>',
            "fr" => "<p>Votre expérience est très importante, la partager aiderait d'autres voyageurs à nous connaître. Voulez-vous nous laisser votre avis ?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class='ql-align-center'>Nous apprécions votre temps et merci de nous avoir choisis !</p>",
            "pt" => "<p>Sua experiência é muito importante, compartilhá-la ajudaria outros viajantes a nos conhecerem. Você pode nos deixar sua avaliação?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class='ql-align-center'>Agradecemos seu tempo e obrigado por nos escolher!</p>",
            "it" => "<p>La tua esperienza è molto importante, condividerla aiuterebbe altri viaggiatori a conoscerci. Ci lasci la tua recensione?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class='ql-align-center'>Apprezziamo il tuo tempo e grazie per averci scelto!</p>",
            "de" => "<p>Ihre Erfahrung ist sehr wichtig, das Teilen würde anderen Reisenden helfen, uns kennenzulernen. Möchten Sie uns Ihre Bewertung hinterlassen?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class='ql-align-center'>Wir schätzen Ihre Zeit und danken Ihnen, dass Sie uns gewählt haben!</p>",
            "ca" => '<p>La teva experiència és molt important, compartir-la ajudaria altres viatgers a conèixer-nos. Ens deixes la teva ressenya?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">Agraïm el teu temps i gràcies per haver-nos triat!</p>',
            "eu" => '<p>Zure esperientzia oso garrantzitsua da, eta partekatzeak bidaiari beste batzuei gu ezagutzen lagunduko die. Utziko diguzu zure iritzia?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">Eskertzen dugu zure denbora eta mila esker gu aukeratzeagatik!</p>',
            "gl" => '<p>A túa experiencia é moi importante, compartila axudaría a outros viaxeiros a coñecernos. ¿Déixanos a túa opinión?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos o teu tempo e grazas por elixirnos!</p>',
            "nl" => '<p>Jouw ervaring is erg belangrijk, het delen ervan zou andere reizigers helpen ons te leren kennen. Wil je ons je recensie achterlaten?</p><p><br></p><p><strong>[Link a las OTAs]</strong></p><p><br></p><p class="ql-align-center">We waarderen je tijd en danken je dat je voor ons gekozen hebt!</p>'
        ];
        $requestSettings->otas_enabled = [
            "booking" => true,
            "expedia" => true,
            "google" => true,
            "tripadvisor" => true,
            "airbnb" => true
        ];
        $requestSettings->request_to = json_encode(['GOOD','VERYGOOD']);
        return $requestSettings;
    }
}

// ===============================

if (! function_exists('saveImage')) {
    function saveImage($image, $model, $id = null, $type = null, $withname = false, $customname = null) {

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

if (! function_exists('generateQr')) {
    function generateQr($concept, $content) {

        $qr = QrCode::format('png')->size(600)->generate($content);
        // Definir el nombre del archivo con una marca de tiempo única
        $nombreArchivo = 'qr_' . $concept . '.png';

        // Definir la ruta completa donde se guardará el QR en S3
        $rutaArchivo = 'qrcodes/' . $nombreArchivo;

        if (Storage::disk('s3')->exists($rutaArchivo)) {
            Storage::disk('s3')->delete($rutaArchivo);
        }

        $storage = Storage::disk('s3')->put($rutaArchivo, $qr, 'public');

        // Obtener la URL pública del archivo guardado
        return $urlQr = Storage::disk('s3')->url($rutaArchivo);
    }
}

if (! function_exists('formatTypeLodging')) {
    function formatTypeLodging($type, $title = false) {

        $typeLodging = [
            "hotel" => !$title ? "hotel" : "Hotel",
            "hostal" => !$title ? "hostal" : "Hostal",
            "at" => !$title ? "apartamento" : "Apartamento",
            "vft" => !$title ? "apartamento" : "Apartamento",
        ];
        $defaultLetter = !$title ? "alojamiento" : "Alojamiento";
        return $typeLodging[$type] ?? $defaultLetter;
    }
}


if (!function_exists('sendMessageDiscord')) {
    function sendMessageDiscord($data) {
        $discordService = new DiscordService();
        $discordService->sendMessage($data['title'] ?? 'No title', $data['message'] ?? 'No message');
    }
}

if (!function_exists('translateQualification')) {
    function translateQualification($qualification, $period = 'in-stay') {
        $texts = [
            'GOOD' => 'Buen' . ($period == 'in-stay' ? 'o' : 'a'),
            'VERYGOOD' => 'Muy buen' . ($period == 'in-stay' ? 'o' : 'a'),
            'WRONG' => 'Mal' . ($period == 'in-stay' ? 'o' : 'a'),
            'VERYWRONG' => 'Muy mal' . ($period == 'in-stay' ? 'o' : 'a'),
            'NORMAL' => 'Normal' . ($period == 'in-stay' ? 'o' : 'a'),
        ];
        return $texts[$qualification] ?? $qualification;
    }
}

/* if (! function_exists('saveDocument')) {
    function saveDocument($file, $folder = 'documents', $customName = null) {
        $storage_env = config('app.storage_env');
        $rand = mt_Rand(1000000, 9999999);
        $ext = '.'.$file->extension();
        $time = time();

        // Generate file name
        $fileName = $customName ? $customName.$ext : $time.'-'.$rand.$ext;

        // Define save path
        $savePath = 'storage/'.$folder.'/'.$fileName;

        // Save based on environment
        if($storage_env == "test" || $storage_env == "pro") {
            // For S3 storage
            $content = file_get_contents($file->getRealPath());
            Storage::disk('s3')->put($savePath, $content, 'public');
        } else {
            // For local storage
            $file->move(public_path('storage/'.$folder.'/'), $fileName);
        }

        return '/'.$savePath;
    }
} */

if (! function_exists('saveDocumentOrImage')) {
    function saveDocumentOrImage($file, $model, $id = null, $type = null) {
        // Get file extension and mime type
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        // Define image types and document types
        $imageTypes = ['jpg', 'jpeg', 'png'];
        $documentTypes = ['pdf'];

        // Check if it's an image
        if (in_array($extension, $imageTypes)) {
            // Use existing saveImage function for images
            return saveImage($file, $model, $id, $type);
        }
        // Check if it's a document
        else if (in_array($extension, $documentTypes)) {
            $storage_env = config('app.storage_env');
            $rand = mt_Rand(1000000, 9999999);
            $time = time();
            $name_file = $time.'-'.$rand.'.'.$extension;
            $savePath = 'storage/'.$model.'/'.$name_file;

            if($storage_env == "test" || $storage_env == "pro") {
                // For S3 storage
                $content = file_get_contents($file->getRealPath());
                Storage::disk('s3')->put($savePath, $content, 'public');
            } else {
                // Ensure directory exists
                $directory = public_path('storage/'.$model);
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                // For local storage
                $file->move($directory, $name_file);
            }

            // Return only the filename
            return $name_file;
        }
        // Unsupported file type
        else {
            throw new \Exception("Unsupported file type: $extension");
        }
    }
}

