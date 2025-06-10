<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuestResource;
use App\Http\Resources\StayResource;
use App\Models\Guest;
use App\Services\ChainService;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;
use App\Services\GuestService;
use App\Services\HotelService;
use App\Services\AuthService;

use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
class GuestAuthController extends Controller
{
    public $service;
    public $chainServices;
    public $hotelServices;
    public $authService;
    function __construct(
        GuestService $_GuestService,
        ChainService $_ChainService,
        HotelService $_HotelService,
        AuthService $_AuthService
    )
    {
        $this->service = $_GuestService;
        $this->chainServices = $_ChainService;
        $this->hotelServices = $_HotelService;
        $this->authService = $_AuthService;
    }

    public function registerOrLogin(Request $request){
        try {
            // buildUrlWebApp('cadena');
            $type = $request->type;
            $guest = null;
            $data = [];
            switch ($type) {
                case 'email':
                    $guest = $this->findByEmail($request->email);
                    break;
                case 'google':
                        $data['redirect'] = buildUrlWebApp('cadena','slug','completar-registro');
                        $guest = $this->getDataByGoogle($data);
                        break;
                default:
                    # code...
                    break;
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, true);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.registerOrLogin');
        }
    }

    public function autenticateByGoogle(Request $request){
        // AUTHENTICATION
        $guestModel = $this->service->findByGoogleId($request->googleId);
        if(!$guestModel) {
            return bodyResponseRequest(EnumResponse::NOT_FOUND, ['message' => 'No se encontró el huesped']);
        }
        $this->authService->login($guestModel, 'session-guest');
        $token = $this->authService->createToken($guestModel, 'session-guest');
        $guestData = new GuestResource($guestModel);
        $data = [
            'token' => $token,
            'guest' => $guestData
        ];
        return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
    }

    public function autenticateByFacebook(Request $request){
        // AUTHENTICATION
        $guestModel = $this->service->findByFacebookId($request->facebookId);
        if(!$guestModel) {
            return bodyResponseRequest(EnumResponse::NOT_FOUND, ['message' => 'No se encontró el huesped']);
        }
        $this->authService->login($guestModel, 'session-guest');
        $token = $this->authService->createToken($guestModel, 'session-guest');
        $guestData = new GuestResource($guestModel);
        $data = [
            'token' => $token,
            'guest' => $guestData
        ];
        return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
    }   



    public function updateById(Request $request){
        try {

            $model = $this->service->updateById($request, true);//true para borrar datos antiguos al momento de registrar

            $this->authService->login($model, 'session-guest');
            $token = $this->authService->createToken($model, 'session-guest');

            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $guestData = new GuestResource($model);
            $data = [
                'token' => $token,
                'guest' => $guestData
            ];
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateById');
        }
    }

    public function confirmPassword(Request $request){
        try {
            // AUTHENTICATION
            $checkCredentials = $this->authService->checkCredentials($request, 'session-guest');
            if(!$checkCredentials) {
                return bodyResponseRequest(EnumResponse::UNAUTHORIZED, ['message' => 'Introduzca credenciales válidas']);
            }
            $guest = $this->authService->getModel($request, 'session-guest');
            $token = $this->authService->createToken($guest);
            

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'token' => $token,
                'guest' => $guest,
            ]);
        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.confirmPassword');
        }
    }

    public function sendResetLinkEmail(Request $request){
        try {
            $request->validate(['email' => 'required|email']);

            $chainSubdomain = $request->attributes->get('chainSubdomain');
            $chain = $this->chainServices->findBySubdomain($chainSubdomain);

            $hotel = $request->attributes->get('hotel');
            $hotelSlug = $hotel->subdomain ?? null;

            $model = $this->service->sendResetLinkEmail($request->email, $hotel, $chain);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.confirmPassword');
        }
    }

    public function resetPassword(Request $request){
        try {
            $request->validate(['token' => 'required','newPassword' => 'required']);
            
            $model = $this->service->resetPassword($request->token, $request->newPassword);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.confirmPassword');
        }
    }

    public function getDataByGoogle(Request $request)
    {
        

        // Include 'chainId' in the state parameter
        $stateData = [
            'chainSubdomain' => $request->chainSubdomain,
            'subdomain' => $request->subdomain ?? null,
            'hotelId' => $request->hotelId ?? null,
            'stayId' => $request->stayId ?? null,
        ];

        $state = base64_encode(json_encode($stateData));

        // Pass only the 'state' parameter
        return Socialite::driver('google')->stateless()->with(['state' => $state])->redirect();
    }

    
    public function handleGoogleCallback(Request $request)
    {
        try {
            Log::info('handleGoogleCallback');
            // Obtener y decodificar el parámetro state para extraer la URL de redirección
            $state = $request->input('state');
            if (!$state) {
                throw new \Exception('State parameter is missing.');
            }

            $decodedState = json_decode(base64_decode($state), true);
            $chainSubdomain = $decodedState['chainSubdomain'];
            $subdomainHotel = $decodedState['subdomain'] === 'null' ? null : $decodedState['subdomain'];
            $hotelId = $decodedState['hotelId'] === 'null' ? null : $decodedState['hotelId'];
            $stayId = $decodedState['stayId'] === 'null' ? null : $decodedState['stayId'];
            $chain = $this->chainServices->findBySubdomain($chainSubdomain);
            $chainId = $chain->id;
            
            // Obtener el usuario autenticado de Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Extraer información del usuario
            $googleId = $googleUser->getId();
            $firstName = $googleUser->user['given_name'] ?? '';
            // $lastName = $googleUser->user['family_name'] ?? '';
            $email = $googleUser->getEmail();
            $avatar = $googleUser->getAvatar();
            
            // Buscar al usuario por email
            $dataGuest = new \stdClass();
            $dataGuest->email = $email;
            $dataGuest->name = $firstName;
            $dataGuest->avatar = $avatar;
            $dataGuest->googleId = $googleId;
            $dataGuest->complete_checkin_data = false;
            // Log::info('$avatar '.$avatar);
            
            // $findGuest = $this->service->findByEmail($email);
            
            $guest = $this->service->saveOrUpdate($dataGuest);
            
            $findValidLastStay = $this->service->findAndValidLastStay($email, $chainId, $hotelId);
            // Log::info('handleGoogleCallback 4 '.json_encode($findValidLastStay));
            if(isset($findValidLastStay["stay"])){
                $stay = $findValidLastStay["stay"];
                $hotel = $this->hotelServices->findById($stay->hotel_id);
                // agregamos el googleId para que se pueda usar en el login
                $redirectUrl = buildUrlWebApp($chainSubdomain, $hotel->subdomain, null,"g={$guest->id}&e={$stay->id}&action=toLogin&gid={$googleId}");
            }else{
                if(!$hotelId){
                    $subdomainHotel = null;
                }
                if($stayId){
                    $findValidLastStay = $this->service->createAccessInStay($guest->id, $stayId, $chainId);
                }
                $redirectUrl = buildUrlWebApp($chainSubdomain, $subdomainHotel, null,"g={$guest->id}&m=google&acform=complete&e={$stayId}");
                Log::info('handleGoogleCallback 7');
            }
            return redirect()->to($redirectUrl);    
        } catch (\Exception $e) {
            Log::error('Error en handleGoogleCallback: ' . $e->getMessage());
            $state = $request->input('state');
            if (!$state) {
                throw new \Exception('State parameter is missing.');
            }

            $decodedState = json_decode(base64_decode($state), true);
            $chainSubdomain = $decodedState['chainSubdomain'];
            $redirectUrl = buildUrlWebApp($chainSubdomain, null, null);
            return redirect()->to("{$redirectUrl}?error=authentication_failed&google=true");
        }
    } 

    public function authWithFacebook(Request $request)
    {
        $stateData = [
            'chainSubdomain' => $request->chainSubdomain,
            'subdomain' => $request->subdomain ?? null,
            'hotelId' => $request->hotelId ?? null,
            'stayId' => $request->stayId ?? null,
        ];

        $state = base64_encode(json_encode($stateData));

        // Redirigir al usuario a Facebook para la autenticación con los permisos y parámetros necesarios
        return Socialite::driver('facebook')
            ->stateless() // Modo sin estado
            ->with(['state' => $state])
            ->redirect();
            // ->scopes(['public_profile', 'email']) // Solicitar permisos necesarios
    }

    public function handleFacebookCallback(Request $request)
    {
        try {
            Log::info('handleFacebookCallback');
            // Obtener y decodificar el parámetro state para extraer la URL de redirección
            $state = $request->input('state');
            if (!$state) {
                throw new \Exception('State parameter is missing.');
            }

            $decodedState = json_decode(base64_decode($state), true);
            $chainSubdomain = $decodedState['chainSubdomain'];
            $subdomainHotel = $decodedState['subdomain'] === 'null' ? null : $decodedState['subdomain'];
            $hotelId = $decodedState['hotelId'] === 'null' ? null : $decodedState['hotelId'];
            $stayId = $decodedState['stayId'] === 'null' ? null : $decodedState['stayId'];
            $chain = $this->chainServices->findBySubdomain($chainSubdomain);
            $chainId = $chain->id;

            // Obtener el usuario autenticado de Facebook
            $facebookUser = Socialite::driver('facebook')->stateless()->user();
            
            // Extraer información del usuario
            $facebookId = $facebookUser->getId();
            $firstName = $facebookUser->user['name'] ?? '';
            $lastName = $facebookUser->user['last_name'] ?? '';
            $email = $facebookUser->getEmail();
            $avatar = $facebookUser->getAvatar();
            if(!$email){
                $redirectUrl = buildUrlWebApp($chainSubdomain, $subdomainHotel ?? null);
                return redirect()->to("{$redirectUrl}?error=unaffiliated-mail");
            }
            // $avatar = $facebookUser->attributes['avatar_original'] ?? 'avatarnulo';
            // Buscar al usuario por email
            $dataGuest = new \stdClass();
            $dataGuest->email = $email;
            $dataGuest->lastname = $lastName;
            $dataGuest->name = $firstName;
            $dataGuest->avatar = $avatar;
            $dataGuest->facebookId = $facebookId;
            $dataGuest->complete_checkin_data = false;
            // Log::info('$avatar '.$avatar);
          
          // $findGuest = $this->service->findByEmail($email);
          
          $guest = $this->service->saveOrUpdate($dataGuest);
          
          $findValidLastStay = $this->service->findAndValidLastStay($email, $chainId, $hotelId);
          // Log::info('handleGoogleCallback 4 '.json_encode($findValidLastStay));
          if(isset($findValidLastStay["stay"])){
              $stay = $findValidLastStay["stay"];
              $hotel = $this->hotelServices->findById($stay->hotel_id);
              $redirectUrl = buildUrlWebApp($chainSubdomain, $hotel->subdomain, null,"g={$guest->id}&e={$stay->id}&action=toLogin&fid={$facebookId}");
          }else{
              if(!$hotelId){
                  $subdomainHotel = null;
              }
              if($stayId){
                  $findValidLastStay = $this->service->createAccessInStay($guest->id, $stayId, $chainId);
              }
              $redirectUrl = buildUrlWebApp($chainSubdomain, $subdomainHotel, null,"g={$guest->id}&m=facebook&acform=complete&e={$stayId}"); 
          }
          return redirect()->to($redirectUrl); 
        } catch (\Exception $e) {
            // Manejar errores y redirigir con un mensaje de error
            Log::error('Error en handleFacebookCallback: ' . $e->getMessage());
            // Obtener y decodificar el parámetro state para extraer la URL de redirección
            $state = $request->input('state');
            if (!$state) {
                throw new \Exception('State parameter is missing.');
            }

            $decodedState = json_decode(base64_decode($state), true);
            $chainSubdomain = $decodedState['chainSubdomain'];
            $redirectUrl = buildUrlWebApp($chainSubdomain, null, null);
            return redirect()->to("{$redirectUrl}?error=authentication_failed&facebook=true");
        }
    }


    public function deleteFacebookData(Request $request)
    {
        Log::info('deleteFacebookData');
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

    public function autenticateGuestDefault (Request $request){
        try {
            $hotelModel = $request->attributes->get('hotel');
            $hotelWithDemoStay = $this->hotelServices->findByParams((Object) ['id' => $hotelModel->id, 'stayDemo' => true]);
            $guestModel = Guest::find($hotelWithDemoStay['demo_stay']['guest_id']);
            if (!$guestModel) {
                return bodyResponseRequest(EnumResponse::NOT_FOUND, ['message' => 'No se encontró el huesped']);
            }
            $this->authService->login($guestModel, 'session-guest');
            $token = $this->authService->createToken($guestModel, 'session-guest');
            $guestData = new GuestResource($guestModel);
            $data = [
                'token' => $token,
                'guest' => $guestData
            ];
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.autenticateGuest');
        }
    }

}
