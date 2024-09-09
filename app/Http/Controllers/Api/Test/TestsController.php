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
    protected $wsdl;
    protected $cert;
    protected $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->wsdl = storage_path('app/wsdl/comunicacion.wsdl'); // Ubica tu WSDL aquí
        $this->cert = storage_path('app/cert/PRE_SGSICS.SES.MIR.ES.cer'); // Ubica tu certificado aquí
        $this->twilio = $twilio;
    }

    

    /* Prueba de SES Hospedaje */
    public function enviarParteDelViajero(Request $request)
{
    // Datos del viajero a enviar. Ajusta los datos conforme al XSD altaParteHospedaje.xsd
    $data = [
        'cabecera' => [
            'codigoArrendador' => '1234567890', // Código asignado por el sistema
            'aplicacion' => 'TuAplicacion', // Nombre de la aplicación
            'tipoOperacion' => 'A', // 'A' para Alta
            'tipoComunicacion' => 'PV', // 'PV' para parte de viajeros
        ],
        'solicitud' => [
            'comunicacion' => [
                [
                    'establecimiento' => [
                        'codigo' => '1234567890', // Código del establecimiento
                        'datosEstablecimiento' => [
                            'tipo' => 'HOSPEDAJE', // Tipo de establecimiento
                            'nombre' => 'Hotel Ejemplo', // Nombre del establecimiento
                            'direccion' => [
                                'pais' => 'ESP', // País
                                'provincia' => 'Madrid', // Provincia
                                'localidad' => 'Madrid', // Localidad
                                'calle' => 'Calle Ejemplo, 123', // Dirección
                                'codigoPostal' => '28001', // Código postal
                            ],
                        ],
                    ],
                    'contrato' => [
                        'referencia' => 'ABC123456', // Número de referencia del contrato
                        'fechaContrato' => '2024-08-31', // Fecha de formalización AAAA-MM-DD
                        'fechaEntrada' => '2024-09-01T15:00:00', // Fecha y hora de entrada
                        'fechaSalida' => '2024-09-05T11:00:00', // Fecha y hora de salida
                        'numPersonas' => 2, // Número de personas
                        'numHabitaciones' => 1, // Número de habitaciones
                        'internet' => true, // Conexión a internet
                    ],
                    'persona' => [
                        [
                            'rol' => 'VI', // Rol (viajero)
                            'nombre' => 'Juan', // Nombre
                            'apellido1' => 'Pérez', // Primer apellido
                            'apellido2' => 'Gómez', // Segundo apellido
                            'tipoDocumento' => 'DNI', // Tipo de documento
                            'numeroDocumento' => '12345678Z', // Número de documento
                            'fechaNacimiento' => '1980-05-15', // Fecha de nacimiento
                            'nacionalidad' => 'ESP', // Nacionalidad (ISO 3166-1 Alfa-3)
                            'sexo' => 'M', // Sexo
                            'direccion' => [
                                'pais' => 'ESP',
                                'provincia' => 'Madrid',
                                'localidad' => 'Madrid',
                                'calle' => 'Calle Falsa, 123',
                                'codigoPostal' => '28001',
                            ],
                            'telefono' => '600123456', // Teléfono de contacto
                            'correo' => 'juan.perez@example.com', // Correo electrónico
                        ]
                    ]
                ]
            ]
        ]
    ];

    try {
        // Convertir el array a XML
        $xmlData = new \SimpleXMLElement('<peticion/>');
        $this->arrayToXml($data, $xmlData);

        // Guardar el XML en un archivo temporal
        $tempFile = tempnam(sys_get_temp_dir(), 'xml');
        $xmlData->asXML($tempFile);

        // Comprimir el archivo XML en un ZIP
        $zip = new \ZipArchive();
        $zipFile = tempnam(sys_get_temp_dir(), 'zip');
        if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
            $zip->addFile($tempFile, 'solicitud.xml');
            $zip->close();
        }

        // Leer el archivo ZIP y codificarlo en Base64
        $encodedZip = base64_encode(file_get_contents($zipFile));

        // Borrar archivos temporales
        unlink($tempFile);
        unlink($zipFile);

        $options = [
            'local_cert' => $this->cert,
            'trace' => 1,
            'exceptions' => true,
            'location' => 'https://hospedajes.pre-ses.mir.es/hospedajes-web/ws/v1/comunicacion' // Endpoint manualmente especificado
        ];

        $client = new SoapClient($this->wsdl, $options);

        // Enviar el ZIP codificado en Base64 como parte de la solicitud
        $response = $client->comunicacion(['peticion' => $encodedZip]);

        return response()->json($response);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

private function arrayToXml($data, &$xmlData)
{
    foreach($data as $key => $value) {
        if(is_array($value)) {
            if(is_numeric($key)){
                $key = 'item'.$key; // dealing with <0/>..<n/> issues
            }
            $subnode = $xmlData->addChild($key);
            $this->arrayToXml($value, $subnode);
        } else {
            $xmlData->addChild("$key",htmlspecialchars("$value"));
        }
    }
}


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

    public function sendMessage(Request $request)
    {
        $this->validate($request, [
            'from' => 'required|string',   // Número remitente
            'to' => 'required|string',     // Número destinatario
            'message' => 'required|string' // Mensaje a enviar
        ]);

        $from = $request->input('from');
        $to = $request->input('to');
        $message = $request->input('message');

        try {
            $this->twilio->sendWhatsAppMessage($from, $to, $message);
            return response()->json(['status' => 'Message sent successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error sending message', 'error' => $e->getMessage()], 500);
        }
    }
}
