<?php

namespace App\Console\Commands;

use App\Services\Hoster\CloneHotelServices;
use App\Services\CloneFacilityService;
use App\Services\Hoster\CloneHotel\CloneLegalHotel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\Hoster\CloneHotel\CloneTriggersCommunicationsHotel;
class GenerateCopyHotelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generatecopyhotel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    public $cloneHotelServices;
    public $cloneFacilityService;
    public $cloneLegalHotel;
    public $cloneTriggersCommunicationsHotel;
    public function __construct(
        CloneHotelServices $_CloneHotelServices,
        CloneFacilityService $_CloneFacilityService,
        CloneLegalHotel $_CloneLegalHotel,
        CloneTriggersCommunicationsHotel $_CloneTriggersCommunicationsHotel
    )
    {
        parent::__construct();
        $this->cloneHotelServices = $_CloneHotelServices;
        $this->cloneFacilityService = $_CloneFacilityService;
        $this->cloneLegalHotel = $_CloneLegalHotel;
        $this->cloneTriggersCommunicationsHotel = $_CloneTriggersCommunicationsHotel;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $codeDiff = Carbon::now()->timestamp;
        $stringDiff = 'B';

        $originalHotel = $this->cloneHotelServices->findOriginalHotel();
        Log::info('originalHotel '.json_encode($originalHotel, JSON_PRETTY_PRINT));

        $copyChain = $this->cloneHotelServices->CreateChainToCopyHotel($originalHotel, $stringDiff);
        Log::info('copyChain '.json_encode($copyChain, JSON_PRETTY_PRINT));

        $copyHotel = $this->cloneHotelServices->CreateCopyHotel($originalHotel, $stringDiff, $copyChain);
        Log::info('copyHotel '.json_encode($copyHotel, JSON_PRETTY_PRINT));

        $copyUser = $this->cloneHotelServices->CreateCopyOwnerUser($originalHotel, $codeDiff, $copyChain, $copyHotel);
        Log::info('copyUser '.json_encode($copyUser, JSON_PRETTY_PRINT));


        /* $updateTrialStays = $this->cloneHotelServices->UpdateTrialStays($originalHotel, $copyHotel, $copyChain);
        Log::info('updateTrialStays '.json_encode($updateTrialStays)); */

        //$updateTrialStays = $this->cloneHotelServices->UpdateTrialStays($originalHotel, $copyHotel, $copyChain);

       /*  $copyCustomization = $this->cloneHotelServices->CopyCustomization($originalHotel->id, $copyHotel->id, $copyChain->id);
        Log::info('copyCustomization '.json_encode($copyCustomization)); */

        //$this->cloneFacilityService->handle($originalHotel->id, $copyHotel->id);

        // politicas y normas generales del hotel
        $this->cloneLegalHotel->handle($originalHotel->id, $copyHotel->id);
        Log::info('cloneLegalGeneral Legal general y normas del hotel clonado');

        // triggers de comunicaciones del hotel
        $this->cloneTriggersCommunicationsHotel->cloneHotelCommunications($originalHotel->id, $copyHotel->id);
        Log::info('cloneTriggersCommunicationsHotel Triggers de comunicaciones del hotel clonado');
    }


}
