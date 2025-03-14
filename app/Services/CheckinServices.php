<?php
namespace App\Services;

use App\Jobs\TranslateGenericMultipleJob;
use App\Models\CheckinSetting;
use App\Models\Hotel;
use App\Utils\Enums\EnumsStay\CheckinSettingsDefaultEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckinServices {

    
    public $languageServices;

    function __construct(
        LanguageServices $_LanguageServices,
    )
    {
        $this->languageServices = $_LanguageServices;
    }

    public function getAllSettings ($hotelId) {
        try {
            $default = CheckinSetting::where('hotel_id',$hotelId)->first();
            if(!$default){
                $default = CheckinSettingsDefaultEnum::defaultFieldsForm();
            }
            return $default;
        } catch (\Exception $e) {
            return $e;
        }
    }
  
 

    public function callAzureFormRecognizer($fileContent, $mimeType)
    {
        try {
            $endpoint = config('services.azure.form_recognizer_endpoint');
            $apiKey   = config('services.azure.form_recognizer_key');

            $analyzeUrl = $endpoint . '/formrecognizer/documentModels/prebuilt-idDocument:analyze?api-version=2022-08-31';

            // 1) Llamada a Azure
            $response = Http::withHeaders([
                    'Ocp-Apim-Subscription-Key' => $apiKey,
                    'Content-Type' => $mimeType,
                ])
                ->withBody($fileContent, $mimeType)
                ->post($analyzeUrl);

            // 2) Polling si es 202
            if ($response->status() == 202) {
                $operationLocation = $response->header('Operation-Location');
                do {
                    sleep(1); 
                    $analysisResponse = Http::withHeaders([
                        'Ocp-Apim-Subscription-Key' => $apiKey
                    ])->get($operationLocation);

                    $analysisJson = $analysisResponse->json();
                    $status = $analysisJson['status'] ?? null;
                } while ($analysisResponse->ok() && $status && !in_array($status, ['succeeded','failed']));

                if ($status === 'succeeded') {
                    $finalResult = $analysisJson;
                    $mrzData = $this->parseMrzFromAzureResponse($finalResult);
                    return [
                        'analysisResult' => $finalResult,
                        'mrzData'        => $mrzData,
                    ];
                } else {
                    throw new \Exception('La operación falló o no se pudo completar');
                }
            }

            if ($response->failed()) {
                Log::error('Azure request failed. Status: '.$response->status().' Body: '.$response->body());
                throw new \Exception('Azure request failed: ' . $response->body());
            }

            // Caso 200 directo
            $analysisJson = $response->json();
            $mrzData = $this->parseMrzFromAzureResponse($analysisJson);

            return [
                'analysisResult' => $analysisJson,
                'mrzData'        => $mrzData,
            ];
        } catch (\Exception $e) {
            Log::error('Error en callAzureFormRecognizer: ' . $e->getMessage());
            return $e;
        }
    }

    private function parseMrzFromAzureResponse(array $analysisJson){
        if (!isset($analysisJson['analyzeResult'])) {
            return null;
        }

        $analyzeRes = $analysisJson['analyzeResult'];
        $pages = $analyzeRes['pages'] ?? [];
        
        // Detectamos docType (passport / nationalIdentityCard / etc.)
        $azureDocType = $analyzeRes['documents'][0]['docType'] 
            ?? null; // p.ej. "idDocument.passport"

        $mrzCandidates = [];
        foreach ($pages as $page) {
            // Log::info("page => ".json_encode($page));
            // paragraphs
            // if (!empty($page['paragraphs'])) {
            //     foreach ($page['paragraphs'] as $p) {
            //         $content = $p['content'] ?? '';
            //         if (strpos($content, '<<<') !== false) {
            //             $mrzCandidates[] = $content;
            //         }
            //     }
            // }
            // lines
            if (!empty($page['lines'])) {
                foreach ($page['lines'] as $l) {
                    $content = $l['content'] ?? '';
                    if (strpos($content, '<<') !== false) {
                        $mrzCandidates[] = $content;
                    }
                }
            }
        }

        Log::info("mrzCandidates => ".json_encode($mrzCandidates));
        Log::info("azureDocType => ".$azureDocType);

        if (empty($mrzCandidates) || !count($mrzCandidates)) {
            return null;
        }
        // Según docType, parseamos
        if ($azureDocType === 'idDocument.passport') {
            $parseo = $this->parseMrzPassport($mrzCandidates);
        } elseif ($azureDocType === 'idDocument.nationalIdentityCard') {
            $parseo = $this->parseMrzDni($mrzCandidates, false);
        }else{
            $parseo = $this->parseMrzDni($mrzCandidates, true);
        }

        // Fallback si no se detectó docType
        // return $this->parseMrz($mrzString , $azureDocType);
        return $parseo;
    }

    
    private function parseMrzPassport($mrzCandidates)
    {
        $docType = 'Pasaporte';
        Log::info("//");
        Log::info("parseMrzPassport");
        $nationalityEXPISO = substr($mrzCandidates[0], 2, 3);
        $nationalityISO = substr($mrzCandidates[1], 10, 3);
        //
        $searchNationality = $nationalityISO ? $this->findCountryName($nationalityISO) : '';
        $nationality = $searchNationality->translateCountry->es ?? null;  
        //
        $birthDate = $this->parseBirthDate($mrzCandidates, $docType, $nationalityEXPISO);
        //
        $names = $this->parseNamesAndSurnames($mrzCandidates[0], $nationalityISO);
        //
        $doscNumber = $this->extractDocumentNumber($mrzCandidates[1]);
        //
        $sex = $this->parseSex($mrzCandidates, $docType);
        

        return $this->mapDataMRZ($docType, $birthDate, $nationality, $names, $doscNumber, $sex);    
    }

    private function parseSex(array $mrzCandidates, string $docType)
    {
        // Verificar que exista la segunda línea
        if (!isset($mrzCandidates[1])) {
            return null;
        }
        
        $mrzLine = $mrzCandidates[1];
        
        if ($docType === 'Pasaporte') {
            // En pasaporte TD3, el sexo está en la posición 21 (0-indexado 20)
            $sex = substr($mrzLine, 20, 1);
        } elseif ($docType === 'DNI' || $docType === 'NIE') {
            // En DNI/NIE (TD1), el sexo está en la posición 8 (0-indexado 7)
            $sex = substr($mrzLine, 7, 1);
        } else {
            return null;
        }
        
        // Si el valor es el carácter de relleno '<', se interpreta como no especificado
        return ($sex === '<') ? null : $sex;
    }


    private function extractDocumentNumber(string $mrzLine)
    {
        // Extraer los primeros 9 caracteres (campo del número de documento)
        $rawDocNumber = substr($mrzLine, 0, 9);
        // Eliminar el relleno '<'
        $documentNumber = str_replace('<', '', $rawDocNumber);
        return $documentNumber;
    }

    private function parseDocumentNumberDNI(array $mrzCandidates)
    {
        // Verificar que exista la primera línea y tenga al menos 15 caracteres
        if (!isset($mrzCandidates[0]) || strlen($mrzCandidates[0]) < 15) {
            return null;
        }
        
        $docLine = $mrzCandidates[0];
        
        // Extraer los 9 caracteres que corresponden al número de documento
        // Posiciones 6 a 14 (0-indexado: desde el índice 5 con longitud 9)
        $rawDocNumber = substr($docLine, 5, 9);
        
        // Eliminar el relleno con el carácter '<'
        $documentNumber = str_replace('<', '', $rawDocNumber);
        
        return $documentNumber;
    }


    private function parseNamesAndSurnames(string $mrzString, string $nationality)
    {
        // Buscar la posición del código de la nacionalidad en el string
        $posNationality = strpos($mrzString, $nationality);
        if ($posNationality === false) {
            return null; // No se encontró el código de la nacionalidad
        }
        
        // Extraer la parte del string que sigue a la nacionalidad (el código ocupa 3 caracteres)
        $dataAfterNationality = substr($mrzString, $posNationality + 3);
        $dataAfterNationality = trim($dataAfterNationality);
        
        // Se espera que la parte de apellidos y nombres estén separados por '<<'
        $parts = explode("<<", $dataAfterNationality);
        
        // La primera parte corresponde a los apellidos y la segunda a los nombres
        $surnamePart = $parts[0] ?? '';
        $namesPart   = $parts[1] ?? '';
        
        // Procesar los apellidos: se separan por el caracter '<'
        $surnameParts = explode("<", $surnamePart);
        // Limpiar espacios y valores vacíos
        $surnameParts = array_values(array_filter(array_map('trim', $surnameParts)));
        
        $firstSurname  = $surnameParts[0] ?? null;
        $secondSurname = $surnameParts[1] ?? null;
        
        // Procesar los nombres: se separan por '<'
        $namesPart = trim($namesPart);
        $nameParts = explode("<", $namesPart);
        // Limpiar espacios y valores vacíos
        $nameParts = array_values(array_filter(array_map('trim', $nameParts)));
        
        $firstName  = $nameParts[0] ?? null;
        $secondName = $nameParts[1] ?? null;
        
        return [
            'first_surname'  => $firstSurname,
            'second_surname' => $secondSurname,
            'first_name'     => $firstName,
            'second_name'    => $secondName,
        ];
    }

    private function parseNamesAndSurnamesDNI(string $mrzString)
    {
        // Limpiar la cadena de posibles espacios en blanco extremos
        $mrzString = trim($mrzString);
        
        // Se espera que la parte de apellidos y nombres esté separada por '<<'
        $parts = explode("<<", $mrzString);
        
        // La primera parte corresponde a los apellidos y la segunda a los nombres
        $surnamePart = $parts[0] ?? '';
        $namesPart   = $parts[1] ?? '';
        
        // Procesar los apellidos: se separan por el carácter '<'
        $surnameParts = explode("<", $surnamePart);
        // Se eliminan espacios y valores vacíos
        $surnameParts = array_values(array_filter(array_map('trim', $surnameParts)));
        
        $firstSurname  = $surnameParts[0] ?? null;
        $secondSurname = $surnameParts[1] ?? null;
        
        // Procesar los nombres: se separan por '<'
        $nameParts = explode("<", $namesPart);
        $nameParts = array_values(array_filter(array_map('trim', $nameParts)));
        
        $firstName  = $nameParts[0] ?? null;
        $secondName = $nameParts[1] ?? null;
        
        return [
            'first_surname'  => $firstSurname,
            'second_surname' => $secondSurname,
            'first_name'     => $firstName,
            'second_name'    => $secondName,
        ];
    }


    private function parseMrzDni($mrzCandidates, $isNIE = false)
    {
        $docType = $isNIE ? 'NIE' : 'DNI';
        Log::info("//");
        Log::info("parseMrzDni");
        $nationalityEXPISO = substr($mrzCandidates[0], 2, 3);
        $nationalityISO = substr($mrzCandidates[1], 15, 3);
        //
        $searchNationality = $nationalityISO ? $this->findCountryName($nationalityISO) : '';
        $nationality = $searchNationality->translateCountry->es ?? null;  
        //
        $birthDate = $this->parseBirthDate($mrzCandidates, $docType, $nationalityEXPISO);
        //
        $names = $this->parseNamesAndSurnamesDNI($mrzCandidates[2]);
        //
        $doscNumber = $this->parseDocumentNumberDNI($mrzCandidates);
        //
        $sex = $this->parseSex($mrzCandidates, $docType);        
        //
        $supportNumber = $this->parseSupportNumberDNI($mrzCandidates);        
        
        return $this->mapDataMRZ($docType, $birthDate, $nationality, $names, $doscNumber, $sex, $supportNumber);  
    }

    private function parseSupportNumberDNI(array $mrzCandidates)
    {
        // Verificar que exista la primera línea
        if (!isset($mrzCandidates[0])) {
            return null;
        }
        
        // Tomamos la primera línea y la limpiamos de espacios extra al inicio y al final
        $line1 = trim($mrzCandidates[0]);
        
        // Nos aseguramos de que la línea tenga al menos 30 caracteres (formato TD1)
        if (strlen($line1) < 30) {
            return null;
        }
        
        // Extraer el campo opcional: posiciones 16 a 30 (0-indexado: desde índice 15, longitud 15)
        $supportRaw = substr($line1, 15, 15);
        
        // Eliminar los caracteres de relleno '<' y también limpiar espacios extra
        $supportNumber = trim(str_replace('<', '', $supportRaw));
        
        // Si el resultado está vacío, se asume que no se usó ese campo
        return $supportNumber !== '' ? $supportNumber : null;
    }

    private function parseBirthDate(array $mrzCandidates, string $docType, ?string $nationalityISO = null)
    {
        // Verificar que exista la segunda línea
        if (!isset($mrzCandidates[1])) {
            return null;
        }
        
        $mrzLine = $mrzCandidates[1];
        
        // Extraer el campo de fecha según el tipo de documento
        if ($docType === 'Pasaporte') {
            // Para pasaporte se requiere el código de la nacionalidad
            if (!$nationalityISO) {
                return null;
            }
            // Buscar la posición del código de la nacionalidad en la segunda línea
            $posNationality = strpos($mrzLine, $nationalityISO);
            if ($posNationality === false) {
                return null;
            }
            // La fecha de nacimiento se extrae 3 caracteres después de la nacionalidad
            $birthDateRaw = substr($mrzLine, $posNationality + 3, 6);
        } elseif ($docType === 'DNI' || $docType === 'NIE') {
            // En el DNI la fecha de nacimiento está al inicio de la línea (primeros 6 caracteres)
            $birthDateRaw = substr($mrzLine, 0, 6);
        } else {
            // Si se pasa otro tipo de documento, se puede retornar null o manejar otro caso
            return null;
        }
        
        // Extraer año, mes y día (formato YYMMDD)
        $yearTwoDigits = substr($birthDateRaw, 0, 2);
        $month         = substr($birthDateRaw, 2, 2);
        $day           = substr($birthDateRaw, 4, 2);
        
        // Obtener el año actual en dos dígitos (por ejemplo, "25" para 2025)
        $currentYearTwoDigits = date('y');
        
        // Si el año extraído es mayor que el actual, se asume que es del siglo XX; de lo contrario, del XXI.
        if ((int)$yearTwoDigits > (int)$currentYearTwoDigits) {
            $fullYear = '19' . $yearTwoDigits;
        } else {
            $fullYear = '20' . $yearTwoDigits;
        }
        
        // Crear la fecha completa y formatearla a DD/MM/YYYY utilizando Carbon
        $date = Carbon::createFromFormat('Ymd', $fullYear . $month . $day);
        return $date->format('d/m/Y');
    }

    

    private function findCountryName($iso3code)
    {
        $filePath = storage_path('app/phone-codes.json');
        $jsonData = file_get_contents($filePath);
        // Decodifica el JSON como objeto
        $phoneCodes = json_decode($jsonData);
        
        $iso3code = strtolower($iso3code);
        
        $result = collect($phoneCodes)->first(function ($phoneCode) use ($iso3code) {
            return isset($phoneCode->iso3code) && strtolower($phoneCode->iso3code) === $iso3code;
        });
        
        return $result ?: null;
    }



    private function mapDataMRZ($doctype, $birthDate, $nationality, $names, $doscNumber, $sex, $supportNumber = null)
    {
        $mapSex = [
            'M' => 'Hombre',
            'F' => 'Mujer'
        ];

        if($doctype == 'DNI' &&  $nationality == "España"){
            $doctype = 'DNI español';
        }else if($doctype == 'DNI' &&  $nationality !== "España"){
            $doctype = null;
        }

        return [
            'DateOfBirth'=> $birthDate,
            'DocumentType_translated'=> $doctype,
            'nationality'=> $nationality,
            'FirstName' => "{$names['first_name']} {$names['second_name']}",
            'lastname' => $names['first_surname'] ?? null,
            'secondLastname' => $names['second_surname'] ?? null,
            'docNumber' => $doscNumber,
            'Sex_translated' => $sex ? $mapSex[$sex] : null,
            'docSupportNumber' => $supportNumber,
        ];
    }

    
    
    // public function callAzureFormRecognizer($fileContent, $mimeType){
    //     try {
    //         $endpoint = config('services.azure.form_recognizer_endpoint');
    //         $apiKey   = config('services.azure.form_recognizer_key');
            
    //         $analyzeUrl = $endpoint . '/formrecognizer/documentModels/prebuilt-idDocument:analyze?api-version=2022-08-31';
            
    //         // 1. Llamada inicial de análisis (POST)
    //         $response = Http::withHeaders([
    //                 'Ocp-Apim-Subscription-Key' => $apiKey,
    //                 'Content-Type' => $mimeType,
    //             ])
    //             ->withBody($fileContent, $mimeType)
    //             ->post($analyzeUrl);
            
    //         // 2. Polling si la respuesta es 202 (análisis asíncrono)
    //         if ($response->status() == 202) {
    //             $operationLocation = $response->header('Operation-Location');
            
    //             do {
    //                 sleep(1); 
    //                 $analysisResponse = Http::withHeaders([
    //                     'Ocp-Apim-Subscription-Key' => $apiKey
    //                 ])->get($operationLocation);
            
    //                 $analysisJson = $analysisResponse->json();
    //                 $status = $analysisJson['status'] ?? null;
            
    //             } while ($analysisResponse->ok() && $status && !in_array($status, ['succeeded','failed']));
            
    //             if ($status === 'succeeded') {
    //                 // 3. ¡Tenemos el resultado final de Form Recognizer!
    //                 //    Extraer y mapear los campos:
    //                 return $analysisJson;
    //                 // return $this->parseIdDocumentFields($analysisJson);
    //             } else {
    //                 throw new \Exception('La operación falló o no se pudo completar');
    //             }
    //         }
    
    //         // Manejo de errores si no es asíncrono (poco común con este modelo)
    //         if ($response->failed()) {
    //             Log::error('Azure Form Recognizer request failed. Status: '.$response->status().' Body: '.$response->body());
    //             throw new \Exception('Azure Form Recognizer request failed: ' . $response->body());
    //         }
            
    //         // Si por algún motivo respondiera 200 con datos directamente:
    //         // return $this->parseIdDocumentFields($response->json());
    //         return $response->json();
    
    //     } catch (\Exception $e) {
    //         Log::error('Error en callAzureFormRecognizer: ' . $e->getMessage());
    //         return $e;
    //     }
    // }
    
    // /**
    //  * Toma el JSON final (con status=succeeded) y devuelve sólo { fields, totalFields }
    //  */
    // private function parseIdDocumentFields(array $analysisJson)
    // {
    //     $documents = $analysisJson['analyzeResult']['documents'] ?? [];
    //     if (empty($documents)) {
    //         return [
    //             'fields' => [],
    //             'totalFields' => 0,
    //         ];
    //     }

    //     // Tomamos el primer documento
    //     $docFields = $documents[0]['fields'] ?? [];
    //     $mappedFields = [];

    //     // Mapa para traducir DocumentType a tus valores
    //     $docTypeMap = [
    //         'PC' => 'Pasaporte',
    //         'ID' => 'DNI español',
    //         'IE' => 'NIE',
    //         // Agrega otros códigos si es necesario
    //     ];

    //     $sexMap = [
    //         'M' => 'Masculino',
    //         'F' => 'Femenino'
    //         // Si hay más letras por convención, podrías agregarlas aquí
    //     ];

    //     foreach ($docFields as $fieldName => $fieldInfo) {
    //         // 1. Detectar el tipo y extraer el valor base
    //         $type = $fieldInfo['type'] ?? 'string';
    //         $value = null;

    //         switch ($type) {
    //             case 'string':
    //                 $value = $fieldInfo['valueString'] ?? null;
    //                 break;
    //             case 'date':
    //                 $value = $fieldInfo['valueDate'] ?? null;
    //                 break;
    //             case 'countryRegion':
    //                 $value = $fieldInfo['valueCountryRegion'] ?? null;
    //                 break;
    //             case 'number':
    //                 $value = $fieldInfo['valueNumber'] ?? null;
    //                 break;
    //             case 'object':
    //                 // Ej: MachineReadableZone
    //                 $value = $fieldInfo['valueObject'] ?? null;
    //                 break;
    //             default:
    //                 $value = null;
    //         }

    //         // 2. Guardar el campo si tiene valor
    //         if (!is_null($value)) {
    //             $mappedFields[$fieldName] = $value;

    //             // 3. Si es NATIONALITY, buscamos su "name" y/o prefijo telefónico
    //             if ($fieldName === 'Nationality') {
    //                 $language = $this->languageServices->getLangByISO3Code($value);
    //                 if ($language) {
    //                     $mappedFields['Nationality_name'] = $language->name;
    //                     // Si tienes un prefijo en la misma tabla, por ejemplo:
    //                     // $mappedFields['phone_prefix'] = $language->phone_prefix;
    //                 }
    //             }

    //             // 4. Si es DocumentType, traducimos a tus tres tipos
    //             if ($fieldName === 'DocumentType') {
    //                 // Buscamos en el mapa; si no existe, dejamos el valor original
    //                 $mappedFields['DocumentType_translated'] = $docTypeMap[$value] ?? $value;
    //             }

    //             // 5. Si es Sex, traducimos a Masculino/Femenino/Otro
    //             if ($fieldName === 'Sex') {
    //                 $mappedFields['Sex_translated'] = $sexMap[$value] ?? 'Otro';
    //             }
    //         }
    //     }

    //     // Contamos cuántos campos hemos mapeado
    //     $totalFields = count($mappedFields);

    //     return [
    //         'fields' => $mappedFields,
    //         'totalFields' => $totalFields
    //     ];
    // }

    

}
