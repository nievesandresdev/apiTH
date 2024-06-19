<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function showResetForm($token)
    {
        return view('auth.passwords.reset', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $record = DB::table('password_reset')->where([
            ['token', $request->token],
            ['email', $request->email],
        ])->first();

        if (!$record || Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return response()->json(['message' => 'El token de restablecimiento de contraseña es inválido o ha expirado.'], 400);
        }

        $user = DB::table('users')->where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'No encontramos un usuario con ese correo electrónico.'], 404);
        }

        DB::table('users')->where('email', $request->email)->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_reset')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Tu contraseña ha sido restablecida!']);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;
        $user = DB::table('users')->where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'No encontramos un usuario con ese correo electrónico.'], 404);
        }

        $token = Str::random(60);
        $created_at = Carbon::now();

        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => $created_at]
        );

        // Enviar correo electrónico con el token
        $user = \App\Models\User::where('email', $email)->first();
        $user->notify(new ResetPasswordNotification($token));

        return response()->json(['message' => 'Enlace de restablecimiento de contraseña enviado a tu correo.']);
    }

    public function verifyToken(Request $request)
    {
        $request->validate(['token' => 'required', 'email' => 'required|email']);

        $record = DB::table('password_reset')->where([
            ['token', $request->token],
            ['email', $request->email],
        ])->first();

        if (!$record || Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return response()->json(['message' => 'El token de restablecimiento de contraseña es inválido o ha expirado.'], 400);
        }

        return response()->json(['message' => 'El token es válido.'], 200);
    }
}

