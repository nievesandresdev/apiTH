<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\Chatgpt\TranslateService;

class TranslateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 2400;
    public $tries = 1;
    protected $dirTemplate;
    protected $inputsTranslate;
    protected $service;
    protected $model;

    public function __construct($dirTemplate, $inputsTranslate, $service, $model)
    {
        $this->dirTemplate = $dirTemplate;
        $this->inputsTranslate = $inputsTranslate;
        $this->service = $service;
        $this->model = $model;
    }

    public function handle()
    {
        \Log::info("handle TranslateJob:");
        // try {
        //     if (empty($this->model)) {
        //         \Log::error("no existe el modelo");
        //     }
        //     ini_set('max_execution_time', '2400');
            
        //     $translateService = new TranslateService();
        //     $responseRranslation = $translateService->load([
        //         'dirTemplate' => $this->dirTemplate,
        //         'context' => $this->inputsTranslate,
        //         'languageCodes' => getAllLanguages(),
        //     ]);
        //     $translation = $responseRranslation['translation'] ?? [];
        //     $this->service->updateTranslation($this->model, $translation);

        // } catch (\Exception $e) {
        //     \Log::error("handle TranslateJob:", ['exception' => $e]);
        // }
    }

}
