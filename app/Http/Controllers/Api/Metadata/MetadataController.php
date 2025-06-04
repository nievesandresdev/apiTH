<?php

namespace App\Http\Controllers\Api\Metadata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;

class MetadataController extends Controller
{
 /**
     * Obtiene los metadatos para un hotel específico
     *
     * @param string $subdomain El identificador único del hotel
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($subdomain)
    {
        // Busca el hotel por su subdomain
        $hotel = Hotel::where('subdomain', $subdomain)->first();
        
        // Si no se encuentra el hotel, devuelve un 404
        if (!$hotel) {
            return response()->json([
                'error' => 'Hotel no encontrado'
            ], 404);
        }
        
        // Devuelve los datos estructurados para los meta tags
        return response()->json([
            'success' => true,
            'data' => [
                'title' => $hotel->name,
                'description' => $hotel->description,
                'image' => config('app.url_bucket').$hotel->image,
                'url' => 'https://' . $subdomain . '.thehoster.app/',
                'hotel_name' => $hotel->name,
            ]
        ]);
    }
}
