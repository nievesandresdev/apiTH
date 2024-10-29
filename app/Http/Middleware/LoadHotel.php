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
        
        //subdimio de cadena agregado desde la webapp
        if ($request->header('chainSubdomain')) {
            $request->attributes->add(['chainSubdomain' => $request->header('chainSubdomain')]);
        }
        //
        if (!$request->header('subdomainHotel')) {
            return $next($request);
        }
        $hotelSubdomain = $request->header('subdomainHotel');
        // $modelHotel = Hotel::where('subdomain', $hotelSubdomain)->first();
        $modelHotel = Hotel::where('subdomain',  $hotelSubdomain)->first();
        // $modelHotel = Hotel::whereHas('subdomains', function($query) use($hotelSubdomain){
        //     $query->where('name', $hotelSubdomain);
        // })->first();

        $data = new HotelResource($modelHotel);

        $request->attributes->add(['hotel' => $data]);

        return $next($request);
    }
}
