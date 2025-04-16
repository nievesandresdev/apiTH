<?php

namespace App\Console\Commands;

use App\Services\Hoster\CloneHotelServices;
use App\Services\CloneFacilityService;
use App\Services\Hoster\CloneHotel\{CloneLegalHotel, CloneTriggersCommunicationsHotel, CloneConfigGeneral, CloneRewardsHotel, CreateInifiteStay};
use App\Services\Hoster\CloneHotel\User\{ProfileUserClone, WorkPositionClone};
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
    public $cloneTriggersCommunicationsHotel;
    public $cloneConfigGeneral;
    public $cloneRewardsHotel;
    public $cloneProfileUser;
    public $cloneWorkPosition;
    public $createInfiniteStay;

    public function __construct(
        CloneHotelServices $_CloneHotelServices,
        CloneFacilityService $_CloneFacilityService,
        CloneLegalHotel $_CloneLegalHotel,
        CloneTriggersCommunicationsHotel $_CloneTriggersCommunicationsHotel,
        CloneConfigGeneral $_CloneConfigGeneral,
        CloneRewardsHotel $_CloneRewardsHotel,
        ProfileUserClone $_CloneProfileUser,
        WorkPositionClone $_CloneWorkPosition,
        CreateInifiteStay $_CreateInifiteStay
    )
    {
        parent::__construct();
        $this->cloneHotelServices = $_CloneHotelServices;
        $this->cloneFacilityService = $_CloneFacilityService;
        $this->cloneLegalHotel = $_CloneLegalHotel;
        $this->cloneTriggersCommunicationsHotel = $_CloneTriggersCommunicationsHotel;
        $this->cloneConfigGeneral = $_CloneConfigGeneral;
        $this->cloneRewardsHotel = $_CloneRewardsHotel;
        $this->cloneProfileUser = $_CloneProfileUser;
        $this->cloneWorkPosition = $_CloneWorkPosition;
        $this->createInfiniteStay = $_CreateInifiteStay;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $codeDiff = '974'; //email
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

        $this->cloneHotelServices->SyncTranslateCopyHotel($originalHotel, $copyHotel);
        $this->cloneHotelServices->CleanRealStaysInCopyHotel($copyHotel);
        $this->cloneHotelServices->UpdateChatSettingsInCopyHotel($originalHotel, $copyHotel);
        $this->cloneHotelServices->UpdateCheckinSettingsInCopyHotel($originalHotel, $copyHotel);
        $this->cloneHotelServices->UpdateQuerySettingsInCopyHotel($originalHotel, $copyHotel);
        $this->cloneHotelServices->UpdateRequestSettingsInCopyHotel($originalHotel, $copyHotel);
        $this->cloneHotelServices->SyncGalleryImagesAndHotelImages($originalHotel, $copyHotel);
        $this->cloneHotelServices->SyncWifiNetworks($originalHotel, $copyHotel);

        $copyCustomization = $this->cloneHotelServices->CopyCustomization($originalHotel->id, $copyHotel->id, $copyChain->id);
        Log::info('copyCustomization '.json_encode($copyCustomization));

        $this->cloneFacilityService->handle($originalHotel->id, $copyHotel->id);
        Log::info('cloneFacilityService Facility del hotel clonado');

        // politicas y normas generales del hotel
        $this->cloneLegalHotel->handle($originalHotel->id, $copyHotel->id);
        Log::info('cloneLegalGeneral Legal general y normas del hotel clonado');

        // triggers de comunicaciones del hotel
        $this->cloneTriggersCommunicationsHotel->cloneHotelCommunications($originalHotel->id, $copyHotel->id);
        Log::info('cloneTriggersCommunicationsHotel Triggers de comunicaciones del hotel clonado');

        // configuraciones generales del hotel (subdomain y idioma por defecto)
        $this->cloneConfigGeneral->cloneConfigGeneral($originalHotel->id, $copyHotel->id,$stringDiff);
        Log::info('cloneConfigGeneral Configuraciones generales del hotel clonado');

        // rewards (referentes y referidos) del hotel
        $this->cloneRewardsHotel->handle($originalHotel->id, $copyHotel->id,$copyUser->id);
        Log::info('cloneRewardsHotel Rewards del hotel clonado');

        // posiciones de trabajo del hotel
        $this->cloneWorkPosition->handle($originalHotel->id, $copyHotel->id);
        Log::info('cloneWorkPosition Posiciones de trabajo del hotel clonado');

        // perfil del usuario del hotel
        $this->cloneProfileUser->handle($originalHotel->id, $copyHotel->id, $copyUser->id, $stringDiff);
        Log::info('cloneProfileUser Perfil del usuario del hotel clonado');

        // Crear estancia infinita para el hotel clonado
        $this->createInfiniteStay->handle($copyHotel->id);
        Log::info('Infinite stay created for cloned hotel');
    }
}
