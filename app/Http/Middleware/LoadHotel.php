<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Hotel;

use App\Http\Resources\HotelResource;

class LoadHotel
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->header('hotelsubdomain')) {
            return $next($request);
        }
        $hotelSubdomain = $request->header('hotelsubdomain');
        // $modelHotel = Hotel::where('subdomain', $hotelSubdomain)->first();
        $modelHotel = Hotel::whereHas('subdomains', function($query) use($hotelSubdomain){
            $query->where('name', $hotelSubdomain);
        })->first();

        $data = new HotelResource($modelHotel);

        $request->attributes->add(['hotel' => $data]);

        return $next($request);
    }
}
