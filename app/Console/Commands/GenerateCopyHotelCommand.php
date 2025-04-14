<?php

namespace App\Console\Commands;

use App\Services\Hoster\CloneHotelServices;
use App\Services\CloneFacilityService;
use App\Services\Hoster\CloneHotel\CloneLegalHotel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
    public function __construct(
        CloneHotelServices $_CloneHotelServices,
        CloneFacilityService $_CloneFacilityService,
        CloneLegalHotel $_CloneLegalHotel
    )
    {
        parent::__construct();
        $this->cloneHotelServices = $_CloneHotelServices;
        $this->cloneFacilityService = $_CloneFacilityService;
        $this->cloneLegalHotel = $_CloneLegalHotel;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $codeDiff = Carbon::now()->timestamp;
        $stringDiff = 'B';
        $codeDiff = Carbon::now()->timestamp;
        $stringDiff = 'B';
        $originalHotel = $this->cloneHotelServices->findOriginalHotel();
        Log::info('originalHotel '.json_encode($originalHotel, JSON_PRETTY_PRINT));
        if(!$originalHotel) return 'No existe el Hotel';
        
        $copyChain = $this->cloneHotelServices->CreateChainToCopyHotel($originalHotel, $stringDiff);
        Log::info('copyChain '.json_encode($copyChain, JSON_PRETTY_PRINT));

        $copyHotel = $this->cloneHotelServices->CreateCopyHotel($originalHotel, $stringDiff, $copyChain);
        Log::info('copyHotel '.json_encode($copyHotel, JSON_PRETTY_PRINT));

        $copyUser = $this->cloneHotelServices->CreateCopyOwnerUser($originalHotel, $codeDiff, $copyChain, $copyHotel);
        Log::info('copyUser '.json_encode($copyUser, JSON_PRETTY_PRINT));
        
        $updateTrialStays = $this->cloneHotelServices->UpdateTrialStays($originalHotel, $copyHotel, $copyChain);
        Log::info('updateTrialStays '.json_encode($updateTrialStays, JSON_PRETTY_PRINT));
        
        $this->cloneHotelServices->CleanRealStaysInCopyHotel($copyHotel);
        $this->cloneHotelServices->UpdateChatSettingsInCopyHotel($originalHotel, $copyHotel);
        $this->cloneHotelServices->UpdateCheckinSettingsInCopyHotel($originalHotel, $copyHotel);
        $this->cloneHotelServices->UpdateQuerySettingsInCopyHotel($originalHotel, $copyHotel);
        $this->cloneHotelServices->UpdateRequestSettingsInCopyHotel($originalHotel, $copyHotel);
        $this->cloneHotelServices->SyncGalleryImagesAndHotelImages($originalHotel, $copyHotel);
        $this->cloneHotelServices->SyncWifiNetworks($originalHotel, $copyHotel);

        $copyCustomization = $this->cloneHotelServices->CopyCustomization($originalHotel->id, $copyHotel->id, $copyChain->id);
        Log::info('copyCustomization '.json_encode($copyCustomization)); 

        //$this->cloneFacilityService->handle($originalHotel->id, $copyHotel->id);

        $cloneLegalGeneral = $this->cloneLegalHotel->handle($originalHotel->id, $copyHotel->id);
        Log::info('cloneLegalGeneral '.json_encode($cloneLegalGeneral, JSON_PRETTY_PRINT));
    }


}
