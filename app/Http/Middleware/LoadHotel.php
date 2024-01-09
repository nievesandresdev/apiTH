<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Hotel;

use App\Http\Resources\HotelResource;

class LoadHotel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hotelSubdomain = $request->header('Hotel-SUBDOMAIN');
        $modelHotel = Hotel::where('subdomain', $hotelSubdomain)->first();

        $data = new HotelResource($modelHotel);
    
        $request->attributes->add(['hotel' => $data]);
    
        return $next($request);
    }
}
