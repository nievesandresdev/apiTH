<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Hotel;

use App\Http\Resources\HotelResource;
use Illuminate\Support\Facades\Log;

class LoadHotel
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('LoadHotel ');
        if (!$request->header('subdomainHotel')) {
            return $next($request);
        }
        Log::info('LoadHotel 1');
        $hotelSubdomain = $request->header('subdomainHotel');
        // $modelHotel = Hotel::where('subdomain', $hotelSubdomain)->first();
        Log::info('LoadHotel 2');
        $modelHotel = Hotel::whereHas('subdomains', function($query) use($hotelSubdomain){
            $query->where('name', $hotelSubdomain);
        })->first();
        Log::info('LoadHotel 3');
        $data = new HotelResource($modelHotel);
        Log::info('LoadHotel 4');
        $request->attributes->add(['hotel' => $data]);
        Log::info('LoadHotel 5');
        return $next($request);
    }
}
