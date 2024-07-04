<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\Chatgpt\TranslateService;
use Illuminate\Support\Facades\Log;

class TranslateGenericMultipleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 2400;
    public $tries = 1;
    protected $arrToTranslate;
    protected $service;
    protected $model;

    public function __construct($arrToTranslate, $service, $model)
    {
        $this->arrToTranslate = $arrToTranslate;
        $this->service = $service;
        $this->model = $model;
    }

    public function handle()
    {
        Log::info("handle TranslateJob: prueba");
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
                    'languageCodes' => getAllLanguages(),
                ]);
                $translation = $responseRranslation['translation'] ?? [];
                $result[$key] = $translation;
            }
            $this->service->updateTranslation($this->model, $result);

        } catch (\Exception $e) {
            Log::error("handle TranslateJob:", ['exception' => $e]);
        }
    }

}
