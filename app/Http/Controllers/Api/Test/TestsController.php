<?php

namespace App\Http\Controllers\Api\Test;

use App\Http\Controllers\Controller;
use App\Utils\Enums\EnumResponse;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\Test\TestMailer;
use Illuminate\Http\Request;
use App\Models\User;
use SoapClient;
use App\Jobs\TestJob;
use Illuminate\Support\Facades\Log;

class TestsController extends Controller
{

    /* Prueba de aws rekognition, reconocimiento de dos imagenes para obtener si es la misma persona */
    public function verifyFace(Request $request)
        {
            $request->validate([
                'id_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'selfie' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $idImagePath = $request->file('id_image')->store('uploads');
            $selfiePath = $request->file('selfie')->store('uploads');

            $client = new RekognitionClient([
                'region' => 'eu-west-2',
                'version' => 'latest',
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ]
            ]);

            $idImage = fopen(storage_path('app/' . $idImagePath), 'r');
            $selfie = fopen(storage_path('app/' . $selfiePath), 'r');

            $result = $client->compareFaces([
                'SourceImage' => [
                    'Bytes' => stream_get_contents($idImage),
                ],
                'TargetImage' => [
                    'Bytes' => stream_get_contents($selfie),
                ],
                'SimilarityThreshold' => 80,
                ]);

            fclose($idImage);
            fclose($selfie);

            Storage::delete([$idImagePath, $selfiePath]);

            if (count($result['FaceMatches']) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Las imágenes coinciden.',
                    'similarity' => $result['FaceMatches'][0]['Similarity']
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Las imágenes no coinciden.'
                ]);
            }
    }

    /* Cambiar contraseña a usuario */
    public function updtPasswordAdmin(Request $request)
    {
        if ($request->password && $request->email) {
            $user = User::where("email",$request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            return $user;
        }else{
            return "invalid";
        }
        

        
    }

    /*PROCESO PARA WHATSAPP BUSINESS*/
    /* INICIO DE SESION */
    public function getShortLivedAccessToken()
    {
        $response = Http::retry(3, 100)->withQueryParameters([
            'client_id' => env('FB_CLIENT_ID'),
            'client_secret' => env('FB_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
        ])->get('https://graph.facebook.com/oauth/access_token');

        if ($response->successful()) {
            $data = $response->json(); 
            return $data;
        } else {
            return response()->json(['error' => 'Error al obtener el token de acceso', 'details' => $response->body()], 500);
        }
    }

    /* ENVIAR MENSAJE */

    public function sendWhatsAppMessage(Request $request)
{
    $request->validate([
        'phone_number_id' => 'required|string',    // El ID del número de WhatsApp Business
        'to' => 'required|string',                 // El número de teléfono destinatario en formato internacional
        'message' => 'required|string'             // El mensaje a enviar
    ]);

    $accessToken = env('WHATSAPP_PERMANENT_TOKEN');
    
    if (!$accessToken) {
        return response()->json(['error' => 'El token permanente no está configurado.'], 500);
    }
    
    // URL del endpoint de WhatsApp Business API
    $url = "https://graph.facebook.com/v20.0/{$request->input('phone_number_id')}/messages";
    
    // Llamada a la API para enviar el mensaje
    
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$accessToken}",  // Usar el token permanente
        'Content-Type' => 'application/json'
    ])->withQueryParameters([
        'access_token' => $accessToken
    ])->post($url, [
        'messaging_product' => 'whatsapp',
        'to' => $request->input('to'),
        "recipient_type" => "individual",
        'type' => 'text',
        'text' => [
            'body' => $request->input('message')
        ]
    ]);
    /*
    example with template
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$accessToken}",  // Usar el token permanente
        'Content-Type' => 'application/json'
    ])->withQueryParameters([
        'access_token' => $accessToken
    ])->post($url, [
        'messaging_product' => 'whatsapp',
        'to' => $request->input('to'),
        "recipient_type" => "individual",
        'type' => 'template',
        'template' => [
            "name" => "hello_world",
            "language" => [
                "code"=>"en_US"
            ]
        ]
    ]);
     */
    // Verificar si la solicitud fue exitosa
    if ($response->successful()) {
        return response()->json(['message' => 'Mensaje enviado con éxito', 'response' => $response->json()], 200);
    } else {
        return response()->json(['error' => 'Error al enviar el mensaje', 'details' => $response->body()], 500);
    }
}


/**
 *  OJO: ESTA ES LA DOCUMENTACION PARA ACTUALIZAR EL PERFIL DEL WHATSAPP
 * https://developers.facebook.com/docs/whatsapp/cloud-api/reference/business-profiles/
 *  
 **/
public function updateWhatsAppProfile(Request $request)
{
    $request->validate([
        'phone_number_id' => 'required|string',
        'name' => 'nullable|string|max:256',
        'description' => 'nullable|string|max:512',
        'address' => 'nullable|string|max:256',
        'email' => 'nullable|string|max:256',
        'website' => 'nullable|string|max:256',
    ]);

    $accessToken = env('WHATSAPP_PERMANENT_TOKEN');

    if (!$accessToken) {
        return response()->json(['error' => 'El token permanente no está configurado.'], 500);
    }

    $url = "https://graph.facebook.com/v20.0/{$request->input('phone_number_id')}/whatsapp_business_profile";
    
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$accessToken}", 
        'Content-Type' => 'application/json',
    ])->post($url, [
        'messaging_product' => 'whatsapp',
        'name' => $request->input('name'),
        'description' => $request->input('description'),
        'address' => $request->input('address'),
        'email' => $request->input('email'),
        'website' => $request->input('website'),
    ]);

    if ($response->successful()) {
        return response()->json(['message' => 'Perfil actualizado con éxito', 'response' => $response->json()], 200);
    } else {
        return response()->json(['error' => 'Error al actualizar el perfil', 'details' => $response->body()], 500);
    }
}
public function sendEmail(Request $request)
{
    $email = 'xavierclasesit@gmail.com';
    $sendMail = Mail::to($email)->send(new TestMailer());

    return response()->json([
        'message' => 'Correo enviado correctamente',
        'msg' => $sendMail
        ]);
}
    public function testJob()
    {
        // Mensaje antes de ejecutar el job
        Log::info('Preparando para ejecutar TestJob.');

        // Despachar el job
        TestJob::dispatch();

        return response()->json(['message' => 'TestJob dispatchado. Revisa los logs y la consola.']);
    }
}
