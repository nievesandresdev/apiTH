<?php
namespace App\Services;

use App\Models\Chain;
use App\Models\Hotel;
use Illuminate\Support\Facades\Http;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Log;

class ChainServices
{
    function __construct()
    {

    }

    public function findBySubdomain ($subdomain) {
        try {
            Log::info('$subdomain'.$subdomain);
            return Chain::where('subdomain',$subdomain)->first();
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getHotelsList ($subdomain) {
        try {
            $chain = $this->findBySubdomain($subdomain);
            if($chain){
                return Hotel::where('chain_id',$chain->id)->where('del', 0)->get();
            }
            return [];
        } catch (\Exception $e) {
            return $e;
        }
    }
}
