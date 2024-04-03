<?php
namespace App\Services;


use Illuminate\Support\Facades\Http;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Log;

class ChatGPTService
{
    public function translateQueryMessage($originalMessage, $queryId)
    {
        Log::info('Iniciando traducción de ChatGPT. queryId='.$queryId);
        try {
            // Mensaje de sistema según los requerimientos
            $systemMessage = "Given the following message from a guest at our hostel, perform the following tasks and report the results in JSON format:\n1. Detect the language of the message, using the ISO 639-1 language code.\n2. Translate the message into Spanish.\n3. Translate the message into English.\n\n---\n\nInstructions for the AI:\n\n- First, analyze the linguistic features of the message to detect the language, reporting the detected language using the ISO 639-1 code. \n- Then, translate the message into Spanish and English, based on the detected language.\n- Format your response as a JSON object with the fields \"detectedLanguage\", \"spanishTranslation\", and \"englishTranslation\", providing the respective information for each.\n\nExample JSON response format:\n```json\n{\n  \"detectedLanguage\": \"Detected ISO 639-1 code language goes here\",\n  \"spanishTranslation\": \"Translation into Spanish goes here\",\n  \"englishTranslation\": \"Translation into English goes here\"\n}\n```\n\nEnsure that the translations are clear and accurate, tailored so that both the hostel staff and the guest can understand them without ambiguities.\n";
            $userMessage = "Guest's message: \"$originalMessage\"";

            // Leer el archivo JSON de la función de traducción
            $translationFunctionPath = resource_path('json/chatgpt/translation_function.json');
            $translationFunction = json_decode(file_get_contents($translationFunctionPath), true);
            
            // Asegúrate de usar el modelo correcto: 'gpt-3.5-turbo'
            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $userMessage] // Aquí va el mensaje del usuario sin json_encode
                ],
                // Otras configuraciones deben coincidir con los requerimientos dados
                'temperature' => 1,
                'max_tokens' => 900,
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
                // Añade las funciones y llamada a función como se muestra en los requerimientos
                'functions' => [$translationFunction], 
                'function_call' => ['name' => 'consideration_translation'],
            ];

            $apiKey = config('app.openia_key');
            if (!$apiKey) {
                Log::error('ChatGPT: La clave de la API de OpenAI falta o está vacía.');
                // throw new \Exception('API key missing.');
            }
            
            
            // Log::debug("ChatGPT: Enviando solicitud a OpenAI: ", $data);

            // Llave de API debe ser pasada como 'Authorization' en el header
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', $data);
    
            //registrar error
            if ($response->failed()) {
                Log::error("Respuesta fallida de ChatGPT: ", $response->json());
            }else{
                Log::info('Respuesta recibida con éxito de ChatGPT.');
            }
            
            $responseArray = $response->json(); 
            $argumentsJson = $responseArray['choices'][0]['message']['function_call']['arguments'] ?? null;
            
            $translations = [];
        
            if ($argumentsJson) {
                $arguments = json_decode($argumentsJson, true);
                $detectedLanguage = $arguments['detectedLanguage'];
                $spanishTranslation = $arguments['spanishTranslation'];
                $englishTranslation = $arguments['englishTranslation'];
        
                // Añadimos el mensaje original al JSON de traducciones
                $translations[$detectedLanguage] = $originalMessage;
        
                // Si el idioma detectado no es el original, añadimos las traducciones
                if ($detectedLanguage !== 'en') {
                    $translations['en'] = $englishTranslation;
                }
                if ($detectedLanguage !== 'es') {
                    $translations['es'] = $spanishTranslation;
                }

            } else {
                Log::error("ChatGPT: Could not decode arguments");
                $translations['SinTraduccion'] = $originalMessage;
                $detectedLanguage = "SinTraduccion";
            }
            return [
                "translations" => $translations,
                "responseLang" => $detectedLanguage,
             ];
        } catch (\RequestException $e) {
            // Registra detalles específicos de la excepción de la solicitud
            Log::error("Error en la solicitud de ChatGPT: {$e->getMessage()}", ['stack' => $e->getTraceAsString()]);
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.translateQueryMessage');
        } catch (\Exception $e) {
            // Registra excepciones generales
            Log::error("ChatGPT Excepción general: {$e->getMessage()}", ['stack' => $e->getTraceAsString()]);
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.translateQueryMessage');
        }
        
    }
}
