<?php
namespace App\Services\Hoster;

use App\Models\Chain;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Query;
use App\Models\Stay;
use App\Models\User;
use App\Services\StayAccessService;
use App\Utils\Enums\EnumResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloneHotelServices
{
    public $mailService;
    public $guestService;
    public $stayAccessService;
    public $utilsHosterServices;
    public $querySettingsServices;
    public $utilityService;

    function __construct(
        StayAccessService $_StayAccessService
    )
    {
        $this->stayAccessService = $_StayAccessService;
    }

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

    public function UpdateTrialStays($originalHotel, $copyHotel, $copyChain)
    {
        try {
            // Obtiene las estancias 'trial' del hotel padre y las del hotel hijo
            $trialStays = $originalHotel->stays()->where('trial', 1)->get();

            DB::beginTransaction();

            foreach ($trialStays as $stayParent) {
                if ($stayParent->son_id) {
                    // Si ya se había creado un stay hijo previamente, se intenta recuperarlo
                    $stayChild = Stay::find($stayParent->son_id);
                    
                    if ($stayChild) {
                        // Si se encontró, se actualiza sus atributos copiando los del stay padre
                        // (Ajusta los campos según corresponda)
                        $stayChild->fill($stayParent->toArray());
                        $stayChild->hotel_id = $copyHotel->id;
                        $stayChild->son_id = null;
                        $stayChild->save();
                    } else {
                        // Si el stay hijo no existe (fue eliminado en el hotel hijo),
                        // se recrea usando el mismo id que estaba almacenado en el padre.
                        $stayChild = $stayParent->replicate();
                        // Asigna manualmente el id guardado en el padre
                        $stayChild->id = $stayParent->son_id;
                        $stayChild->hotel_id = $copyHotel->id;
                        $stayChild->son_id = null;
                        // Forzamos la inserción con el id específico.
                        $stayChild->exists = false;
                        $stayChild->save();
                    }
                } else {
                    // Si el stay padre no tiene asignado un hijo, se crea uno nuevo (sin son_id)
                    $stayChild = $stayParent->replicate();
                    $stayChild->hotel_id = $copyHotel->id;
                    $stayChild->son_id = null;
                    $stayChild->save();
                    // Se actualiza el stay padre para registrar el id del stay hijo creado
                    $stayParent->son_id = $stayChild->id;
                    $stayParent->save();
                }

                //actualiza los huéspedes de la estancia
                $this->syncGuestsForStay($stayParent, $stayChild, $copyChain);
            }

            // Se obtienen todos los son_ids actualmente asociados a los trial stays del hotel padre.
            $stayParentSonIds = $originalHotel->stays()->where('trial', 1)->pluck('son_id');

            // Elimina en el hotel hijo aquellos stays que no corresponden a ningún son_id del hotel padre.
            Stay::where('hotel_id', $copyHotel->id)
                ->whereNotIn('id', $stayParentSonIds)
                ->delete();

            DB::commit();

            return $trialStays;
        } catch (\Exception $e) {
            DB::rollBack();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.UpdateTrialStays');
        }
    }

    private function syncGuestsForStay($stayParent, $stayChild, $copyChain){
        try {
            // Inicializa un arreglo para recolectar los IDs de los huéspedes copiados
            $childGuestIds = [];
            
            // Itera cada huésped de la estancia padre
            foreach ($stayParent->guests as $parentGuest) {
                $partesEmail = explode('@', $parentGuest->email);
                // Genera el nuevo email concatenando la primera parte, el timestamp y luego el dominio
                $newEmail = $partesEmail[0] . '.B' . '@' . $partesEmail[1];
                // Verifica si ya se registró un hijo para este huésped
                if ($parentGuest->son_id) {
                    $childGuest = Guest::find($parentGuest->son_id);
                    if ($childGuest) {
                        // Actualiza los datos del huésped copiando la información del huésped padre
                        $childGuest->fill($parentGuest->toArray());
                        $childGuest->email = $newEmail;
                        $childGuest->checkin_email = $newEmail;
                        $childGuest->son_id = null;
                        $childGuest->save();
                    } else {
                        // Si el huésped fue eliminado en el hotel hijo, lo recrea con el mismo ID
                        $childGuest = $parentGuest->replicate();
                        $childGuest->id = $parentGuest->son_id;
                        $childGuest->email = $newEmail;
                        $childGuest->checkin_email = $newEmail;
                        $childGuest->son_id = null;
                        $childGuest->exists = false; // Forzamos el insert con el ID específico
                        $childGuest->save();
                    }
                } else {
                    // Si nunca se copió, se crea por primera vez
                    $childGuest = $parentGuest->replicate();
                    $childGuest->email = $newEmail;
                    $childGuest->checkin_email = $newEmail;
                    $childGuest->son_id = null;
                    $childGuest->save();
                    // Se guarda en el huésped padre el mapeo hacia el hijo
                    $parentGuest->son_id = $childGuest->id;
                    $parentGuest->save();
                }
                $childGuestIds[] = $childGuest->id;
                
                //copia las consultas del huésped padre al huésped hijo
                $this->syncQueriesForGuest($parentGuest, $childGuest, $stayChild);
                // // asignando en la tabla pivote el campo device = 'Movil' para cada acceso copiado.
                // $this->stayAccessService->save($stayChild->id,$childGuest->id,'Movil');
            }

            // Sincroniza la relación en la estancia hija para que tenga exactamente estos huéspedes
            $stayChild->guests()->sync(array_fill_keys($childGuestIds, ['chain_id' => $copyChain->id]));
            
            $guestSonIds = $stayParent->guests()->pluck('son_id');
            $stayParent->guests()->whereNotIn('id', $guestSonIds)->delete();
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.syncGuestsForStay');
        }
    }

    private function syncQueriesForGuest($parentGuest, $guestChild, $stayChild)
    {
        try {
            // Itera cada consulta del huésped padre
            Log::info('parent: '.json_encode($parentGuest->name));
            foreach ($parentGuest->queries as $queryParent) {
                Log::info('queries: '.json_encode($parentGuest->queries));
                // Si ya se copió previamente, busca la consulta hija
                $childQuery = Query::where('stay_id',$stayChild->id)->where('guest_id',$guestChild->id)->where('period',$queryParent->period)->first();
                if ($childQuery) {
                    // Actualiza sus datos copiando los del padre
                    $childQuery->fill($queryParent->toArray());
                    $childQuery->stay_id = $stayChild->id;
                    $childQuery->guest_id = $guestChild->id;
                    $childQuery->save();
                } else {
                    // Si la consulta hija fue eliminada, la recrea usando el mismo ID mapeado
                    $childQuery = $queryParent->replicate();
                    $childQuery->stay_id = $stayChild->id;
                    $childQuery->guest_id = $guestChild->id;
                    $childQuery->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.syncQueriesForGuest');
        }
    }

}
