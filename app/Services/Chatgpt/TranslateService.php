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

use App\Utils\Templates\Translation\PathTemplate;

use OpenAI\Client;
use OpenAI;

class TranslateService {

    function __construct()
    {

    }

    public function load ($data) {
        $withValidation = isset($data["withValidation"]) ? $data["withValidation"]  : true;
        

        if (!isset($data['dirTemplate']) || !isset($data['languageCodes']) || !isset($data['context'])) {
            return;
        }
        // $templates = PathTemplate::getAllowedTemplates();
        $responseTranslate = $this->translate($data);
        ['errorTranslate' => $errorTranslate, 'input' => $input, 'output' => $output, 'translation' => $translation] = $responseTranslate;
        if ($withValidation && empty($errorTranslate) && !empty($translation)) {
            $responseValidate = $this->validate($input, $output);
            ['status' => $status, 'attempts'=>$attempts, 'errorValidate' => $errorValidate] =  $responseValidate;
            if ($status != 200) {
                \Log::error('ERROR_TRANSLATION', ['status' => $status, 'attempts' => $attempts,  'output' => $output]);
            }
            $data = [
                'input' => $input,
                'output' => $output,
                'translation' => $status == 200 ? $translation : [],
                'errorTranslate' => $errorTranslate,
                'status' => $status,
                'attempts' => $attempts,
                'errorValidate' => $errorValidate,
            ];
            return $data;
        }
        $data = [
            'input' => $input,
            'output' => $output,
            'translation' => $translation,
            'errorTranslate' => $errorTranslate,
        ];

        return $data;
    }

    // TRANSLATION

    public function translate ($payload) {
        try {
            $errorTranslate = null;
            $inputTranslation = $this->loadInputTranslation($payload);
            if (!$inputTranslation) null;
            $outputTranslationChagpt = $this->requestChatgpt($inputTranslation);
            if (isset($outputTranslationChagpt['error'])) {
                   $errorTranslate = $outputTranslationChagpt['body'];
            }

            $arguments = $outputTranslationChagpt['choices'][0]['message']['function_call']['arguments'] ?? [];
            $dataTranslate = $arguments ? (object) json_decode($arguments) : [];
            return ['errorTranslate' => $errorTranslate, 'input' => $inputTranslation, 'output' => $outputTranslationChagpt, 'translation' => $dataTranslate];

        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return $e;
        }
    }

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
                // "model" => config('app.azure_openia_deployment'),
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
        $MODEL_DEPLOYMENT = config('app.azure_openia_deployment');
        $BASE_URI = "https://thehoster-test-openai.openai.azure.com/openai/deployments/${MODEL_DEPLOYMENT}";
        try {
            $client = OpenAI::factory()
            ->withBaseUri($BASE_URI)
                ->withApiKey(config('app.azure_openia_key'))
                ->withHttpHeader('api-key', config('app.azure_openia_key'))
                ->withQueryParam('api-version', config('app.azure_openia_version'))
                ->make();
                $response = $client->chat()->create($input);
            return $response;
        } catch (\Exception $e) {
            \Log::error('ERROR_TRANSLATION', ['message' => $e->getMessage()]);
            return;
        }
    }

    // public function requestChatgpt ($input) {
    //     try {

    //         // $client = OpenAI::client(env('OPENAI_API_KEY'));
    //         $headers = [
    //             'Content-Type' => 'application/json',
    //             'Authorization' => 'Bearer ' . config('app.openia_key'),
    //         ];
    //         $http_client_service = new HttpClientService();
    //         $response_request = $http_client_service->make_request('post', 'https://api.openai.com/v1/chat/completions', $input, $headers);
    //         return $response_request;
    //     } catch (\Exception $e) {
    //         var_dump($e->getMessage());
    //         return $e;
    //     }
    // }

    // VALIDATION OUTPUT

    public function validate ($input, $output, $attempts = 0) {
        $status = null;
        $valid = null;
        $errorValidate = null;

        try {

            $attempts++;
            // \Log::info($attempts);

            $inputValidationTranslation = $this->loadInputValidationTranslate($input, $output);
            if (!$inputValidationTranslation) null;
            // return $inputValidationTranslation;
            $outputValidationTranslationChagpt = $this->requestChatgpt($inputValidationTranslation);

            if (isset($outputValidationTranslationChagpt['error'])) {
                $errorValidate = $outputValidationTranslationChagpt['body'];
            }

            $arguments = $outputValidationTranslationChagpt['choices'][0]['message']['function_call']['arguments'] ?? [];
            $dataValidation = $arguments ? json_decode($arguments, true) : [];
            // return $dataValidation;
            $valid = isset($dataValidation['valid']) && gettype($dataValidation['valid']) === 'boolean' ? $dataValidation['valid'] : null;
            
            if ($valid  === true) {
                $status = 200;
            } else if ($valid !== true && $attempts < 3) {
                $status = 300;
            }
            else if ($valid !== true && $attempts >= 3) {
                $status = 500;
            } else {
                $status = 501;
            }

            if ($status === 300) {
                sleep(5);
                return $this->validate($input, $output, $attempts);
            }


            $res = [
                'status' => $status,
                'attempts' => $attempts,
                'errorValidate' => $errorValidate,
            ];
            return $res;

        } catch (\Exception $e) {
            return $e;
        }
    }

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
                // 'model' => config('app.azure_openia_deployment'),
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