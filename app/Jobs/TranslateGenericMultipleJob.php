<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\Chatgpt\TranslateService;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

class TranslateGenericMultipleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 2400;
    public $tries = 1;
    protected $arrToTranslate;
    protected $service;
    protected $model;
    protected $langsToTranslate;
    protected $withValidation;
    public function __construct($arrToTranslate, $service, $model, $langsToTranslate = [], $withValidation = true)
    {
        $this->arrToTranslate = $arrToTranslate;
        $this->service = $service;
        $this->model = $model;
        $this->langsToTranslate = $langsToTranslate;
        $this->withValidation = $withValidation;
    }

    public function handle()
    {
        Log::info("TranslateGenericMultipleJob");
        try {
            if (empty($this->model)) {
                Log::error("no existe el modelo");
            }
            ini_set('max_execution_time', '2400');
            $result = [];
            
            $translateService = new TranslateService();
            foreach ($this->arrToTranslate as $key => $value) {
                $responseRranslation = $translateService->load([
                    'dirTemplate' => 'translation/generic',
                    'context' => ['text' => $value],
                    'languageCodes' => count($this->langsToTranslate) > 0? $this->langsToTranslate: getAllLanguages(),
                    'withValidation' => $this->withValidation
                ]);

                $translation = $responseRranslation['translation'] ?? [];

                if (!$this->validTranslation($translation)) {
                    $result[$key] = [];
                    continue;
                }

                $result[$key] = $translation;
            }
            $this->service->updateTranslation($this->model, $result);

        } catch (\Exception $e) {
            Log::error("handle TranslateJob:", ['exception' => $e]);
        }
    }

    private function validTranslation($translation): bool
    {
        $translation = is_array($translation)
            ? $translation
            : json_decode(json_encode($translation), true);
        
        $validator = Validator::make($translation, [
            '*' => ['required', 'array'],
            '*.text' => ['required', 'string']
        ]);
    
        return !$validator->fails();
    }

}
