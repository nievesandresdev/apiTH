<?php
namespace App\Jobs\Stay;

use App\Services\Hoster\Stay\StaySessionServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

class DeleteStaySessionUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $idJob;
    protected $userEmail;
    protected $stayId;
    protected $staySessionServices;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($idJob, $userEmail, $stayId)
    {
        $this->idJob = $idJob;
        $this->stayId = $stayId;
        $this->userEmail = $userEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(StaySessionServices $_StaySessionServices)
    {
        Log::info('DeleteStaySessionUser job');
        $this->staySessionServices = $_StaySessionServices;
        Log::info('DeleteStaySessionUser llamado a servicios');
        $this->staySessionServices->deleteSession($this->stayId, $this->userEmail);
        Log::info('DeleteStaySessionUser ejecutado servicio');
    }
}
