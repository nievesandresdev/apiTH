<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\Chatgpt\TranslateService;

class TranslateModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 2400;
    public $tries = 1;
    protected $dirTemplate;
    protected $inputsTranslate;
    protected $service;
    protected $model;
    protected $languages;

    public function __construct($dirTemplate, $inputsTranslate, $service, $model, $languages = [])
    {
        $this->dirTemplate = $dirTemplate;
        $this->inputsTranslate = $inputsTranslate;
        $this->service = $service;
        $this->model = $model;
        $this->languages = $languages;
    }

    public function handle()
    {
        try {
            if (empty($this->model)) {
                \Log::error("no existe el modelo");
            }
            ini_set('max_execution_time', '2400');
            
            $translateService = new TranslateService();
            $input = [
                'dirTemplate' => $this->dirTemplate,
                'context' => $this->inputsTranslate,
                'languageCodes' => $this->languages ?? getAllLanguages(),
            ];
            // \Log::info($input);
            $responseTranslation = $translateService->load($input);

            $translation = $responseTranslation['translation'] ?? [];
            $this->service->updateTranslation($this->model, $translation);

        } catch (\Exception $e) {
            \Log::error("handle TranslateJob:", ['exception' => $e]);
        }
    }

}
