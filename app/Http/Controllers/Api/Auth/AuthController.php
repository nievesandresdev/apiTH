<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use App\Utils\Enums\EnumResponse;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::guard('web')->attempt($credentials)) {

            return bodyResponseRequest(EnumResponse::UNAUTHORIZED, ['message' => 'Failed to authenticate']);
        }

        $user = Auth::guard('web')->user();

        //$user->load('hotels');

        $token = $user->createToken('appToken')->accessToken;

        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'token' => $token,
            'user' => new UserResource($user),
            'tyest' => auth()->user()
        ]);
    }

   /*  public function getUsers(Request $request)
    {
        return response()->json($request->user()->load('profile'));
    } */

    public function sendResetLinkEmail(Request $request): RedirectResponse
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
