<?php
namespace App\Services\Hoster;

use App\Models\Chain;
use App\Models\Hotel;
use App\Models\User;
use App\Utils\Enums\EnumResponse;
use Carbon\Carbon;
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
            return Chain::firstOrCreate(
            ['parent_hotel_id'=> $originalHotel->id],
            [
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
                ['parent_id' => $originalHotel->id],
                // Atributos a actualizar/crear: mezclamos todos los atributos del original y luego sobreescribimos los que requieren cambios.
                array_merge($originalHotel->toArray(), [
                    'name' => $newName,
                    'slug' => $originalHotel->slug . $stringDiff,
                    'subdomain' => $originalHotel->subdomain . $stringDiff,
                    'chain_id' => $copyChain->id,
                    'parent_id' => $originalHotel->id
                ])
            );

            return $copiedHotel;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.CreateCopyHotel');
        }
    }

    public function CreateCopyOwnerUser($originalHotel, $code, $copyChain, $copyHotel)
    {
        try {
            
            $userOwnerOriginal = $originalHotel->user()->where('owner',1)->first();
            // Extrae el email original
            $emailOriginal = $userOwnerOriginal->email;
            // Separa el email por el símbolo "@"
            $partesEmail = explode('@', $emailOriginal);
            // Genera el nuevo email concatenando la primera parte, el timestamp y luego el dominio
            $nuevoEmail = $partesEmail[0] . $code . '@' . $partesEmail[1];

             // Usa updateOrCreate para crear o actualizar el hotel
            $copiedUser = User::updateOrCreate(
                ['owner' => 1,'chain_id' => $copyChain->id],
                // Atributos a actualizar/crear: mezclamos todos los atributos del original y luego sobreescribimos los que requieren cambios.
                array_merge($copyHotel->toArray(), [
                    'email' => $nuevoEmail,
                    'password' => $userOwnerOriginal->password
                ])
            );

            $profileData = [
                'firstname' => $copyHotel->name,
            ];
            
            // Si el usuario ya cuenta con un perfil, se actualiza; de lo contrario, se crea uno nuevo
            if ($copiedUser->profile) {
                $copiedUser->profile()->update($profileData);
            } else {
                $copiedUser->profile()->create($profileData);
            }
            
            // Asigna el rol "Associate" al usuario
            $copiedUser->assignRole('Associate');
            
            $copiedUser->hotel()->syncWithoutDetaching([
                $copyHotel->id => [
                    'manager'      => 1,
                    'is_default'   => 1 
                ]
            ]);
            return $copiedUser;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.CreateCopyOwnerUser');
        }
    }

    


}
