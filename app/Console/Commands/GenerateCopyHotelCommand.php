<?php

namespace App\Console\Commands;

use App\Services\Hoster\CloneHotelServices;
use App\Services\Hoster\CloneHotel\CloneGeneralHotel;
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
    public $cloneGeneralHotel;
    public function __construct(
        CloneHotelServices $_CloneHotelServices,
        CloneGeneralHotel $_CloneGeneralHotel
    )
    {
        parent::__construct();
        $this->cloneHotelServices = $_CloneHotelServices;
        $this->cloneGeneralHotel = $_CloneGeneralHotel;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $codeDiff = Carbon::now()->timestamp;
        $stringDiff = 'B';
        $originalHotel = $this->cloneHotelServices->findOriginalHotel();
        $copyChain = $this->cloneHotelServices->CreateChainToCopyHotel($originalHotel, $stringDiff);
        Log::info('copyChain '.json_encode($copyChain, JSON_PRETTY_PRINT));
        $copyHotel = $this->cloneHotelServices->CreateCopyHotel($originalHotel, $stringDiff, $copyChain);
        Log::info('copyHotel '.json_encode($copyHotel, JSON_PRETTY_PRINT));
        $copyUser = $this->cloneHotelServices->CreateCopyOwnerUser($originalHotel, $codeDiff, $copyChain, $copyHotel);
        Log::info('copyUser '.json_encode($copyUser, JSON_PRETTY_PRINT));
        /* pretty json log */
        Log::info('originalHotel '.json_encode($originalHotel, JSON_PRETTY_PRINT));
        $cloneLegalGeneral = $this->cloneGeneralHotel->cloneLegalGeneral($originalHotel->id, $copyHotel->id);
        Log::info('cloneLegalGeneral '.json_encode($cloneLegalGeneral, JSON_PRETTY_PRINT));
       /*  if(!$originalHotel) return 'No existe el Hotel';
        Log::info('originalHotel '.json_encode($originalHotel));
        $copyChain = $this->cloneHotelServices->CreateChainToCopyHotel($originalHotel, $stringDiff);
        Log::info('copyChain '.json_encode($copyChain));
        $copyHotel = $this->cloneHotelServices->CreateCopyHotel($originalHotel, $stringDiff, $copyChain);
        Log::info('copyHotel '.json_encode($copyHotel));
        $copyUser = $this->cloneHotelServices->CreateCopyOwnerUser($originalHotel, $codeDiff, $copyChain, $copyHotel);
        Log::info('copyUser '.json_encode($copyUser));
        $updateTrialStays = $this->cloneHotelServices->UpdateTrialStays($originalHotel, $copyHotel, $copyChain);
        Log::info('updateTrialStays '.json_encode($updateTrialStays));
        $this->cloneGeneralHotel->cloneLegalGeneral($originalHotel->id, $copyHotel->id); */
    }


}
