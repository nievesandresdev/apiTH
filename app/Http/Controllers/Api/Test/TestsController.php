<?php

namespace App\Http\Controllers\Api\Test;

use App\Http\Controllers\Controller;
use App\Utils\Enums\EnumResponse;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use App\Models\User;
use SoapClient;

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
}
