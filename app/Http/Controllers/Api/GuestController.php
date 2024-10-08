<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuestResource;
use App\Http\Resources\StayResource;
use App\Models\Guest;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;
use App\Services\GuestService;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Google\Client as GoogleClient;

class GuestController extends Controller
{
    public $service;

    function __construct(
        GuestService $_GuestService
    )
    {
        $this->service = $_GuestService;
    }

    public function findById ($id) {
        try {
            $model = $this->service->findById($id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = new GuestResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findById');
        }
    }

    public function saveOrUpdate (Request $request) {
        try {
            $model = $this->service->saveOrUpdate($request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = new GuestResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.saveOrUpdate');
        }
    }

    public function updateLanguage (Request $request) {
        try {
            $model = $this->service->updateLanguage($request);
            $data = new GuestResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.saveOrUpdate');
        }
    }

    public function findLastStay($id,Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->findLastStayAndAccess($id,$hotel);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = new StayResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findLastStay');
        }
    }

    public function sendMailTo(Request $request){
        $stayId = $request->stayId;
        $guestId = $request->guestId;
        $guestEmail = $request->guestEmail;
        $hotelId = $request->attributes->get('hotel')->id;

        $data = ['message' => __('response.bad_request_long')];
        if(!$stayId || !$guestId || !$guestEmail) return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);

        $sent = $this->service->sendEmail($stayId,$guestId,$guestEmail,$hotelId);
        return bodyResponseRequest(EnumResponse::ACCEPTED, $sent);
    }

    public function getDataByGoogle(Request $request)
    {
        
        // Obtener la URL de redirección actual para volver después de la autenticación
        $redirectUrl = $request->input('redirect'); // e.g., https://nobuhotelsevillatex.test.thehoster.io
        Log::info('$redirectUrl '. $redirectUrl);


        // Serializar la URL de redirección en el parámetro state
        $state = base64_encode(json_encode(['redirect' => $redirectUrl]));
        Log::info("redirectToGoogle: state: {$state}");

        // Redirigir al usuario a Google para la autenticación con el parámetro state
        return Socialite::driver('google')->stateless()->with(['state' => $state])->redirect();
    }
    
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Obtener y decodificar el parámetro state para extraer la URL de redirección
            $state = $request->input('state');
            if (!$state) {
                throw new \Exception('State parameter is missing.');
            }

            $decodedState = json_decode(base64_decode($state), true);
            $redirectUrl = $decodedState['redirect'] ?? 'https://thehoster.io';

            // Obtener el usuario autenticado de Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Extraer información del usuario
            $googleId = $googleUser->getId();
            $firstName = $googleUser->user['given_name'] ?? '';
            $lastName = $googleUser->user['family_name'] ?? '';
            $email = $googleUser->getEmail();
            $avatar = $googleUser->getAvatar();
            
            $names = $firstName.' '.$lastName;
            
            // Buscar al usuario por email
            $dataGuest = new \stdClass();
            $dataGuest->email = $email;
            $guest = $this->service->saveOrUpdate($dataGuest);
            // $guest = Guest::where('email', $email)->first();
            // Log::info('$guest'. json_encode($guest));
            // Generar un token de autenticación (usando Laravel Sanctum)
            $token = $guest->createToken('auth_token')->plainTextToken;
            // Log::info('$token'. json_encode($token));
            
            // Redirigir de vuelta al subdominio original con el token
            return redirect()->to("{$redirectUrl}?auth_token={$token}&googleId={$googleId}&names={$names}&email={$email}&avatar={$avatar}");
        } catch (\Exception $e) {
            // Manejar errores y redirigir con un mensaje de error
            $state = $request->input('state');
            $decodedState = $state ? json_decode(base64_decode($state), true) : null;
            $redirectUrl = $decodedState['redirect'] ?? 'https://default-subdomain.test.thehoster.io';
            return redirect()->to("{$redirectUrl}?error=authentication_failed");
        }
    }
    
    

    public function authWithFacebook(Request $request)
    {
        // Obtener la URL de redirección desde el frontend
        $redirectUrl = $request->input('redirect'); // Ejemplo: https://subdominio.tu-dominio.com

        // Serializar la URL de redirección en el parámetro state
        $state = base64_encode(json_encode(['redirect' => $redirectUrl]));

        // Redirigir al usuario a Facebook para la autenticación con el parámetro state
        return Socialite::driver('facebook')
            ->stateless() // Indica que la autenticación es stateless
            ->with(['state' => $state])
            ->scopes(['public_profile', 'email']) // Solicitar permisos necesarios
            ->redirect();
    }




    public function handleFacebookCallback(Request $request)
    {
        try {
            // Obtener y decodificar el parámetro state para extraer la URL de redirección
            $state = $request->input('state');
            if (!$state) {
                throw new \Exception('El parámetro state está ausente.');
            }

            $decodedState = json_decode(base64_decode($state), true);
            $redirectUrl = $decodedState['redirect'] ?? 'https://thehoster.io';

            // Obtener el usuario autenticado de Facebook
            $facebookUser = Socialite::driver('facebook')->stateless()->user();
            Log::info('Datos del usuario de Facebook: ' . json_encode($facebookUser->user));
            Log::info('response fb: ' . $facebookUser->user);

            // Extraer información del usuario
            $facebookId = $facebookUser->getId();
            $firstName = $facebookUser->user['first_name'] ?? '';
            $lastName = $facebookUser->user['last_name'] ?? '';
            $email = $facebookUser->getEmail();
            $avatar = $facebookUser->getAvatar();

            $names = $firstName.' '.$lastName;
            
            // Buscar al usuario por email
            $dataGuest = new \stdClass();
            $dataGuest->email = $email;
            $guest = $this->service->saveOrUpdate($dataGuest);
            
            // Generar un token de autenticación (usando Laravel Sanctum)
            $token = $guest->createToken('auth_token')->plainTextToken;

            // Redirigir de vuelta al subdominio original con el token
            return redirect()->to("{$redirectUrl}?auth_token={$token}&facebookId={$facebookId}&names={$names}&email={$email}&avatar={$avatar}");
        } catch (\Exception $e) {
            // Manejar errores y redirigir con un mensaje de error
            Log::error('Error en handleFacebookCallback: ' . $e->getMessage());

            $state = $request->input('state');
            $decodedState = $state ? json_decode(base64_decode($state), true) : null;
            $redirectUrl = $decodedState['redirect'] ?? 'https://tu-dominio.com';

            return redirect()->to("{$redirectUrl}?error=authentication_failed");
        }
    }



    public function deleteFacebookData(Request $request)
    {
        Log::info('deleteFacebookData');
        // Verificar la firma de la solicitud (opcional pero recomendado)
        // $signature = $request->header('x-hub-signature');
        // Log::info('$signature '.json_encode($signature));
        // if (!$this->isValidSignature($request->getContent(), $signature)) {
        //     return response()->json(['error' => 'Firma inválida.'], 403);
        // }

        // Validar la solicitud
        $validated = $request->validate([
            'email' => 'required|email', // Utilizamos el email para identificar al usuario
            'data_deletion' => 'required|string|in:requested',
        ]);

        $email = $validated['email'];

        // Buscar al usuario en la base de datos usando el email
        $guest = Guest::where('email', $email)->first();

        if ($guest) {
            // Anonimizar los datos del usuario
            $guest->update([
                'name' => null,
                'email' => null
            ]);

            // Responder a Facebook para confirmar la eliminación
            return response()->json([
                'result' => 'success',
                'message' => 'Datos de usuario eliminados correctamente.'
            ], 200);
        }

        // Si el usuario no se encuentra, responde con éxito según las especificaciones de Facebook
        return response()->json([
            'result' => 'success',
            'message' => 'Usuario no encontrado, pero se asume que los datos están eliminados.'
        ], 200);
    }

    private function isValidSignature($payload, $signature)
    {
        if (!$signature) {
            return false;
        }

        list($algo, $hash) = explode('=', $signature, 2) + [null, null];

        if ($algo !== 'sha1') {
            return false;
        }

        $appSecret = config('services.facebook.client_secret');
        $computedHash = hash_hmac('sha1', $payload, $appSecret, false);

        // Logs para depuración
        Log::info('Payload: ' . $payload);
        Log::info('Computed Hash: ' . $computedHash);
        Log::info('Received Hash: ' . $hash);

        return hash_equals($hash, $computedHash);
    }


}
