<?php

namespace App\Services\Chatgpt;

use App\Http\Resources\CityResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Services\HttpClientService;

use App\Services\Chatgpt\Templates\GenericTemplate;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\File;

class TranslateService {

    function __construct()
    {

    }

    public function load ($payload) {
        $status = null;
        $valid = null;
        $attempts = 0;
        $errorTranslate = null;
        $errorValidation = null;
        try {


            do {

                $attempts++;

                // var_dump($attempts);

                $inputTranslation = $this->loadInputTranslation($payload);
                if (!$inputTranslation) null;

                $outputTranslationChagpt = $this->requestChatgpt($inputTranslation);
                /*$outputTranslationChagpt = json_decode('{
                    "id": "chatcmpl-9WlgoipYOJyDhMKOb8Bo4DcleJmy8",
                    "object": "chat.completion",
                    "created": 1717596842,
                    "model": "gpt-3.5-turbo-0125",
                    "choices": [
                        {
                            "index": 0,
                            "message": {
                                "role": "assistant",
                                "content": null,
                                "function_call": {
                                    "name": "translation",
                                    "arguments": "{\n    \"es\": {\n        \"description\": \"descripcion prueba\"\n    },\n    \"en\": {\n        \"description\": \"test description\"\n    },\n    \"fr\": {\n        \"description\": \"description de test\"\n    },\n    \"de\": {\n        \"description\": \"Testbeschreibung\"\n    },\n    \"it\": {\n        \"description\": \"descrizione di prova\"\n    },\n    \"pt\": {\n        \"description\": \"descrição de teste\"\n    }\n}"
                                }
                            },
                            "logprobs": null,
                            "finish_reason": "stop"
                        }
                    ],
                    "usage": {
                        "prompt_tokens": 237,
                        "completion_tokens": 101,
                        "total_tokens": 338
                    },
                    "system_fingerprint": null
                }', true);*/
                if (isset($outputTranslationChagpt['error'])) {
                       $errorTranslate = $outputTranslationChagpt['body'];
                }

                $arguments = $outputTranslationChagpt['choices'][0]['message']['function_call']['arguments'] ?? [];
                $dataTranslate = $arguments ? (object) json_decode($arguments) : [];
                    
                $inputValidationTranslation = $this->loadInputValidationTranslate($inputTranslation, $outputTranslationChagpt);
                if (!$inputValidationTranslation) null;

                $outputValidationTranslationChagpt = $this->requestChatgpt($inputValidationTranslation);
                if (isset($outputValidationTranslationChagpt['error'])) {
                    $errorValidation = $outputValidationTranslationChagpt['body'];
                }

                $arguments = $outputValidationTranslationChagpt['choices'][0]['message']['function_call']['arguments'] ?? [];
                $dataValidation = $arguments ? json_decode($arguments, true) : [];
                $valid = $dataValidation['valid'] ?? false;
                if ($valid) {
                    $status = 200;
                } else {
                    $status = 300;
                }

            } while ($attempts < 3 && $status != 200);

            if ($attempts >= 3 && $status != 200) {
                $status = 500;
            }

            $res = [
                'status' => $status,
                'attempts' => $attempts,
                'errorTranslate' => $errorTranslate,
                'errorValidation' => $errorValidation,
                'translate' => $dataTranslate
            ];

            return $res;

        } catch (\Exception $e) {
            $status = 501;
            $res = [
                'status' => $status,
                'error' => $e->getMessage()
            ];
            return $res;
        }
    }

    // TRANSLATION
    public function loadInputTranslation ($payload) {
        try {

            ['dirTemplate' => $dirTemplate, 'context' => $context, 'languageCodes' => $languageCodes] = $payload;

            $baseContext = $this->processTemplateBaseTranslate($payload);
            if (!$baseContext) return;
            $functionContext = $this->processTemplateFunctionTranslate($payload, $baseContext);
            if (!$functionContext) return;
            $messageContext = $this->processTemplateMessageTranslate($payload, $baseContext);
            if (!$messageContext) return;

            $function_call = ['name' => 'translation'];
            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messageContext,
                'functions' => $functionContext,
                'function_call' => $function_call,
                'temperature' => 1,
                'max_tokens' => 4095,
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            ];
            return $data;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function requestChatgpt ($input) {
        try {

            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('app.openia_key'),
            ];
            $http_client_service = new HttpClientService();
            $response_request = $http_client_service->make_request('post', 'https://api.openai.com/v1/chat/completions', $input, $headers);
            return $response_request;
        } catch (\Exception $e) {
            return $e;
        }
    }

    // VALIDATION OUTPUT
    public function loadInputValidationTranslate ($input, $output) {
        try {
            if (!$input || !$output) return;
            $dirTemplate = 'validation';
            $functionContext = $this->processTemplateFunctionValidation($dirTemplate);
            if (!$functionContext) return;
            $messageContext = $this->processTemplateMessageValidation($input, $output);
            if (!$messageContext) return;
            $function_call = ['name' => 'translator_checker'];
            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messageContext,
                'functions' => $functionContext,
                'function_call' => $function_call,
                'temperature' => 1,
                'max_tokens' => 4095,
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            ];
            return $data;

        } catch (\Exception $e) {
            return $e;
        }
    }

    // FUNCTION VALIDATION
    private function processTemplateFunctionValidation ($dirTemplate) {
        try {
            $dirTemplate = 'validation';
            $templateContent = $this->readTemplateFile($dirTemplate, 'function');
            if ($templateContent){
                $templateContent = json_decode($templateContent, true);
                return [$templateContent];
            }
            return;

        } catch (\Exception $e) {
            return $e;
        }
    }

    // MESSAGE VALIDATION
    private function processTemplateMessageValidation ($input, $output) {
        try {
            $dirTemplate = 'validation';
            $context = ['input' => $input, 'output' => $output];
            $templateContent = $this->readTemplateFile($dirTemplate, 'message');
            if ($templateContent){
                $templateContent = json_decode($templateContent, true);
                $templateContent = $this->renderValidationMessageTemplate($templateContent, $context);
                return $templateContent;
            }
            return;
        } catch (\Exception $e) {
            return $e;
        }
    }


    // BASE TRANSLATE

    public function processTemplateBaseTranslate ($payload) {
        try {

            ['dirTemplate' => $dirTemplate, 'context' => $context, 'languageCodes' => $languageCodes] = $payload;

            $baseContent = $this->readTemplateFile($dirTemplate, 'base');
            if ($baseContent) {

                $baseJson = json_decode($baseContent, true);
                foreach ($baseJson as $key => $value) {
                    if (is_string($value) && strpos($value, '{{') !== false && strpos($value, '}}') !== false) {
                        // Mantener las cadenas con plantillas tal como están
                        $context[$key] = $value;
                    } else {
                        // Asignar todos los otros valores al contexto
                        $context[$key] = $value;
                    }
                }

                return $context;

            }

            return;

        } catch (\Exception $e) {
            return $e;
        }
    }

    // FUNCTION TRANSLATE

    private function processTemplateFunctionTranslate ($payload, $baseContext) {
        try {

            ['dirTemplate' => $dirTemplate, 'context' => $context, 'languageCodes' => $languageCodes] = $payload;

            $functionContext = [];

            if (array_key_exists('function_expected_parameter', $baseContext)) {
                $functionContext['function_expected_parameters'] = $this->generateFunctionExpectedParameters(
                    $languageCodes,
                    $baseContext['function_expected_parameter']
                );
            }

            // if (array_key_exists('language_codes', $context)) {
            //     $functionContext['expected_languages_keys'] = $context['language_codes'] ?? [];
            // }
            $functionContext['expected_languages_keys'] = $languageCodes ?? [];

            $templateContent = $this->readTemplateFile($dirTemplate, 'function');
            $templateContent = $templateContent ? json_decode($templateContent, true) : null;

            $content = [];
            if ($templateContent) {
                $content = $this->renderTemplateFunction($templateContent, $functionContext);
                return $content;
            }

            return;

        } catch (\Exception $e) {
            return $e;
        }
    }

    private function renderTemplateFunction ($templateContent, $functionContext) {
        try {
            foreach ($functionContext as $key => $value) {
                if ($key === 'function_expected_parameters') {
                    $templateContent['parameters']['properties'] = $value;
                }
                if ($key === 'expected_languages_keys') {
                    $templateContent['parameters']['required'] = $value;
                }
            }

            return [$templateContent];

        } catch (\Exception $e) {
            return $e;
        }
    }

    private function generateFunctionExpectedParameters ($languagesCodes, $baseTemplate) {
        try {

            $result = [];
            foreach ($languagesCodes as $code) {
                $result[$code] = $baseTemplate['{{ language_code }}'];
            }
            return $result;
            // return json_encode($result, JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return $e;
        }
    }

    // MESSAGE TRANSLATE

    private function processTemplateMessageTranslate ($payload, $baseContext) {
        try {

            ['dirTemplate' => $dirTemplate, 'context' => $context, 'languageCodes' => $languageCodes] = $payload;

            $messageContext = [];

            if (array_key_exists('system_expected_parameter', $baseContext)) {
                $messageContext['system_expected_parameters'] = $this->generateSystemExpectedParameters(
                    $languageCodes,
                    $baseContext['system_expected_parameter']
                );
            }
            $templateContent = $this->readTemplateFile($dirTemplate, 'message');
            $templateContent = $templateContent ? json_decode($templateContent, true) : null;
            $content = [];
            if ($templateContent) {
                $templateContent = $this->renderValidationMessageTemplate($templateContent, $context);
                $templateContent = $this->renderTemplate($templateContent, $messageContext);
                return $templateContent;
            }
            return;

        } catch (\Exception $e) {
            return $e;
        }
    }

    

    private function generateSystemExpectedParameters ($languageCodes, $baseTemplate) {
        try {

            $paramsList = [];
            foreach ($languageCodes as $code) {
                $param = str_replace("{{ language_code }}", $code, $baseTemplate);
                $paramsList[] = $param;
            }
            // return implode(",\n", $paramsList);
            return substr(json_encode(implode(",\n", $paramsList)), 1, -1);

        } catch (\Exception $e) {
            return $e;
        }
    }

    private function renderValidationMessageTemplate($templateContent, $context) {
        foreach ($context as $key => $value) {
            if (!is_string($value)) {
                $value = json_encode($value);
            }
            $templateContent[1]['content'][0]['text'] = str_replace("{{ $key }}", $value, $templateContent[1]['content'][0]['text']);
        }
        
        return $templateContent;
    }

    private function renderTemplate($templateContent, $context) {
        $templateContent = json_encode($templateContent);
        // return $context;
        foreach ($context as $key => $value) {
            if (!is_string($value)) {
                $value = json_encode($value);
            }
            $templateContent = str_replace("{{ $key }}", $value, $templateContent);
        }
        $templateContent = json_decode($templateContent, true);
        // return $templateContent;
        return $templateContent;
    }

    // TREACTMENT FILES

    private function readTemplateFile ($dirTemplate, $typeScheme) {
        try {
            $path = base_path("app/Utils/Templates/Translation/$dirTemplate/$typeScheme.json");

            if (\File::exists($path)) {
                return File::get($path);
            }
            return null;

        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return $e;
        }
    }

}