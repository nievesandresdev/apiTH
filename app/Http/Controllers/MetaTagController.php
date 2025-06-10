<?php

// app/Http/Controllers/MetaTagController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;

class MetaTagController extends Controller
{
    public function getMetaTags(Request $request)
    {
        try {
            $subdomain = $request->header('subdomainHotel');
            $path = $request->get('path', '/'); // Ruta actual del frontend

            // Obtener datos del hotel
            $hotel = Hotel::where('subdomain', $subdomain)->first();

            if (!$hotel) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Hotel not found'
                ], 404);
            }

            // Determinar título y descripción basado en la ruta
            $title = $this->getTitleByPath($hotel->name, $path);
            $description = $this->getDescriptionByPath($hotel->name, $path);

            return response()->json([
                'ok' => true,
                'data' => [
                    'title' => $title,
                    'description' => $description,
                    'image' => $hotel->logo ?? $hotel->image,
                    'url' => $request->get('url'),
                    'type' => 'website',
                    'site_name' => $hotel->name,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getTitleByPath($hotelName, $path)
    {
        // Mapeo de rutas a títulos
        $pathTitles = [
            '/' => "$hotelName | Inicio",
            '/alojamiento' => "$hotelName | Alojamiento",
            '/experiencias' => "$hotelName | Experiencias",
            '/lugares' => "$hotelName | Lugares",
            '/chat' => "$hotelName | Chat",
        ];

        return $pathTitles[$path] ?? "$hotelName | Bienvenido";
    }

    private function getDescriptionByPath($hotelName, $path)
    {
        // Mapeo de rutas a descripciones
        $pathDescriptions = [
            '/' => "Bienvenido a $hotelName. Descubre nuestros servicios y comodidades.",
            '/alojamiento' => "Explora nuestras instalaciones y servicios en $hotelName.",
            '/experiencias' => "Descubre experiencias únicas en $hotelName.",
            '/lugares' => "Conoce lugares increíbles cerca de $hotelName.",
            '/chat' => "Contacta con el servicio de atención al cliente de $hotelName.",
        ];

        return $pathDescriptions[$path] ?? "Bienvenido a $hotelName";
    }
}