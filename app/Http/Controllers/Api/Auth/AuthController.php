<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Utils\Enums\EnumResponse;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

use App\Services\AuthService;

class AuthController extends Controller
{
    public $authService;

    public function __construct(
        AuthService $_AuthService
    ) {
        $this->authService = $_AuthService;
    }

    public function login(Request $request)
    {
        $checkCredentials = $this->authService->checkCredentials($request, 'web');
        if(!$checkCredentials) {
            return bodyResponseRequest(EnumResponse::UNAUTHORIZED, ['message' => 'Introduzca credenciales válidas']);
        }

        $user = $this->authService->getModel($request, 'web');

        // Verificar si el usuario tiene status = 1
        if ($user->status == 0) {
            return bodyResponseRequest(EnumResponse::UNAUTHORIZED, ['message' => 'Su cuenta ha sido inactivada. Solicita acceso a tu responsable o superior para poder entrar.']);
        }

        //verify if del = 1
        if ($user->del == 1) {
            return bodyResponseRequest(EnumResponse::UNAUTHORIZED, ['message' => 'Su cuenta ha sido eliminada. Solicita acceso a tu responsable o superior para poder entrar.']);
        }

        $token = $this->authService->createToken($user);
        
        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function loginByCode(string $code)
    {
        // 1. Buscar usuario por código
        $user = User::where('login_code', $code)->first();
        if (! $user) {
            return bodyResponseRequest(EnumResponse::UNAUTHORIZED, [
                'message' => 'Código inválido'
            ]);
        }

        // 2. Verificar estado y eliminación
        if ($user->status == 0) {
            return bodyResponseRequest(EnumResponse::UNAUTHORIZED, [
                'message' => 'Su cuenta ha sido inactivada. Solicita acceso a tu responsable o superior para poder entrar.'
            ]);
        }
        if ($user->del == 1) {
            return bodyResponseRequest(EnumResponse::UNAUTHORIZED, [
                'message' => 'Su cuenta ha sido eliminada. Solicita acceso a tu responsable o superior para poder entrar.'
            ]);
        }

        // 3. Generar token
        $token = $user->createToken('appToken')->accessToken;

        // 4. Devolver misma respuesta que en login tradicional
        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'token' => $token,
            'user'  => new UserResource($user),
        ]);
    }



    public function loginAdmin(Request $request)
    {

        // Limpiar la sesión existente
        Session::flush();
        Auth::guard('web')->logout();


        // Buscar al usuario por ID
        $user = User::find($request->user);


        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'User not found',
            ], 404);
        }

        // Loguear al usuario directamente
        Auth::guard('web')->login($user, true);

        // Obtener el usuario autenticado
        $authenticatedUser = Auth::guard('web')->user();

        // Crear el token de acceso
        $token = $authenticatedUser->createToken('appToken')->accessToken;

        // Retornar la respuesta con el token y la información del usuario
        return response()->json([
            'error' => false,
            'token' => $token,
            'user' => new UserResource($authenticatedUser),
        ], 200);
    }

    public function getUserData(Request $request)
    {
        // Autenticar al usuario desde el token
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Retornar los datos del usuario
        return response()->json([
            'user' => new UserResource($user),
            'ss' => 'sss',
            /* 'current_hotel' => $user->current_hotel,
            'current_subdomain' => $user->current_hotel->subdomain, */
        ]);
    }


    public function sendResetLinkEmail(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
        ]);
        //dd($request->email);
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 200);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
    public function logout()
    {
        auth()->user()->token()->revoke();

        return bodyResponseRequest(EnumResponse::SUCCESS, ['message' => 'Successfully logged out']);
    }
}
