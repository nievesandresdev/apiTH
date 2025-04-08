<?php
namespace App\Services\Hoster;

use App\Models\Chain;
use App\Models\Hotel;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class CloneHotelServices
{
    public function findOriginalHotel(){

        $hotelId = config('app.hotelId_dossier');
        $originalHotel = DB::table('hotels')->where('id',$hotelId)->first();
        if ($originalHotel) {
            // Convierte el objeto stdClass a un arreglo de atributos
            $attributes = (array)$originalHotel;
            
            // Hydrate crea una colección de modelos; obtenemos el primero
            $originalHotel = Hotel::hydrate([$attributes])->first();
            
            // Asegurarse de marcarlo como existente
            $originalHotel->exists = true;
        }
        return $originalHotel;
    }
    
    public function CreateChainToCopyHotel($originalHotel, $stringDiff){
        try {
            $originalChain = $originalHotel->chain;
            return Chain::firstOrCreate([//68 en milocal
                'subdomain' => $originalChain->subdomain.$stringDiff,
                'type' => $originalChain->type
            ]);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.CreateChainToCopyHotel');
        }
    }

    public function CreateCopyHotel($originalHotel, $stringDiff, $copyChain)
    {
        try {
            // Define el nombre único para la copia
            $newName = $originalHotel->name . $stringDiff;

            // Usa updateOrCreate para crear o actualizar el hotel
            $copiedHotel = Hotel::updateOrCreate(
                // Condición para encontrar la copia, aquí se asume que el nombre debe ser único.
                ['name' => $newName],
                // Atributos a actualizar/crear: mezclamos todos los atributos del original y luego sobreescribimos los que requieren cambios.
                array_merge($originalHotel->toArray(), [
                    'name' => $newName,
                    'slug' => $originalHotel->slug . $stringDiff,
                    'subdomain' => $originalHotel->subdomain . $stringDiff,
                    'chain_id' => $copyChain->id,
                ])
            );

            return $copiedHotel;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.CreateCopyHotel');
        }
    }

    


}
