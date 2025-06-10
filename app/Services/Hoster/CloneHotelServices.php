<?php
namespace App\Services\Hoster;

use App\Models\Chain;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatSettingLanguage;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\ImageGallery;
use App\Models\ImagesHotels;
use App\Models\Query;
use App\Models\Stay;
use App\Models\User;
use App\Services\StayAccessService;
use App\Utils\Enums\EnumResponse;
use App\Utils\Enums\EnumsStay\CheckinSettingsDefaultEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Customization;
use App\Models\HotelTranslate;
use App\Models\HotelWifiNetworks;

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

    public function SyncTranslateCopyHotel($originalHotel, $copyHotel)
    {
        try {
            $originalTranslates = $originalHotel->languageNames;
            foreach ($originalTranslates as $originalTranslate) {
                if ($originalTranslate->son_id) {
                    // Si ya se había creado un translate hijo previamente, se intenta recuperarlo
                    $translateChild = HotelTranslate::find($originalTranslate->son_id);

                    if ($translateChild) {
                        // Si se encontró, se actualiza sus atributos copiando las traducciones padre
                        $translateChild->fill($originalTranslate->toArray());
                        $translateChild->hotel_id = $copyHotel->id;
                        $translateChild->son_id = null;
                        $translateChild->save();
                    } 
                } else {
                    // Si la traduccion padre no tiene asignado un hijo, se crea uno nuevo (sin son_id)
                    $translateChild = $originalTranslate->replicate();
                    $translateChild->hotel_id = $copyHotel->id;
                    $translateChild->son_id = null;
                    $translateChild->save();
                    // Se actualiza la traduccion padre para registrar el id dla traduccion hijo creado
                    $originalTranslate->son_id = $translateChild->id;
                    $originalTranslate->save();
                }
            }
            return true;

            
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.SyncTranslateCopyHotel');
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
                    'parent_id' => null,
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
        Log::info('UpdateTrialStays');
        try {
            
            // Obtiene las estancias 'trial' del hotel padre y las del hotel hijo
            $updateTrialStays = $originalHotel->stays()->where('trial', 1)->get();
            // Log::info('trialStays '.json_encode($trialStays, JSON_PRETTY_PRINT));
            DB::beginTransaction();
            foreach ($updateTrialStays as $stay) {
                $addDayToCheckIn = Carbon::parse($stay->check_in)->addDay()->toDateString();
                $addDayToCheckOut = Carbon::parse($stay->check_out)->addDay()->toDateString();
                $stay->check_in = $addDayToCheckIn;
                $stay->check_out = $addDayToCheckOut;
                $stay->save();
            }
            
            $trialStays = $originalHotel->stays()->where('trial', 1)->get();
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
                $this->syncGuestsForStay($stayParent, $stayChild, $copyChain, $copyHotel);
            }

            // // Se obtienen todos los son_ids actualmente asociados a los trial stays del hotel padre.
            // $stayParentSonIds = $originalHotel->stays()->where('trial', 1)->pluck('son_id')->filter()->toArray();

            // // Elimina en el hotel hijo aquellos stays que no corresponden a ningún son_id del hotel padre.
            // Stay::where('hotel_id', $copyHotel->id)
            //     ->whereNotIn('id', $stayParentSonIds)
            //     ->delete();

            DB::commit();

            return $trialStays;
        } catch (\Exception $e) {
            DB::rollBack();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.UpdateTrialStays');
        }
    }

    private function syncGuestsForStay($stayParent, $stayChild, $copyChain, $copyHotel){
        try {
            // Inicializa un arreglo para recolectar los IDs de los huéspedes copiados
            $childGuestIds = [];

            // Itera cada huésped de la estancia padre
            foreach ($stayParent->guests as $parentGuest) {
                $partesEmail = explode('@', $parentGuest->email);
                // Genera el nuevo email concatenando la primera parte, el timestamp y luego el dominio
                $newEmail = $partesEmail[0] . '.C' . '@' . $partesEmail[1];
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
                $this->syncQueriesForGuest($parentGuest, $childGuest, $stayParent, $stayChild);
                // // asignando en la tabla pivote el campo device = 'Movil' para cada acceso copiado.
                $this->stayAccessService->save($stayChild->id,$childGuest->id,'Movil');

                $this->syncChatsForGuest($parentGuest, $childGuest, $stayParent, $stayChild, $copyHotel);
            }
            // Sincroniza la relación en la estancia hija para que tenga exactamente estos huéspedes
            $stayChild->guests()->sync(array_fill_keys($childGuestIds, ['chain_id' => $copyChain->id]));

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.syncGuestsForStay');
        }
    }

    private function syncQueriesForGuest($parentGuest, $guestChild, $stayParent, $stayChild)
    {
        try {
            // Itera cada consulta del huésped padre
            $parentGuests = $parentGuest->queries()->where('stay_id',$stayParent->id)->get();
            foreach ($parentGuests as $queryParent) {
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

    private function syncChatsForGuest($parentGuest, $childGuest, $stayParent, $stayChild, $copyHotel)
    {
        try {
            // Itera cada chat asociado al huésped padre
            $parentChat = $parentGuest->chats()->where('stay_id',$stayParent->id)->first();
            if(!$parentChat)return;
            // Procesa el chat: si ya tiene asignado un hijo se actualiza, de lo contrario se crea
            $childChat = Chat::where('stay_id',$stayChild->id)->where('guest_id',$childGuest->id)->first();
            if ($childChat) {
                // Actualiza los datos del chat copiando los del registro padre
                $childChat->fill($parentChat->toArray());
                $childChat->guest_id = $childGuest->id;
                $childChat->stay_id  = $stayChild->id;
                $childChat->save();
            } else {
                // Si el chat hijo no existe (fue eliminado), se recrea usando el mismo ID
                $childChat = $parentChat->replicate();
                $childChat->guest_id = $childGuest->id;
                $childChat->stay_id  = $stayChild->id;
                $childChat->save();
            }

            // Sincroniza los mensajes del chat: se hace de forma similar a los pasos anteriores
            foreach ($parentChat->messages as $parentMessage) {
                if ($parentMessage->son_id) {
                    $childMessage = ChatMessage::find($parentMessage->son_id);
                    if ($childMessage) {
                        $childMessage->fill($parentMessage->toArray());
                        $childMessage->chat_id = $childChat->id;
                        $childMessage->messageable_id = $parentMessage->messageable_type === 'App\Models\Guest' ? $childGuest->id : $copyHotel->id;
                        $childMessage->son_id = null;
                        $childMessage->save();
                    } else {
                        $childMessage = $parentMessage->replicate();
                        $childMessage->id = $parentMessage->son_id;
                        $childMessage->chat_id = $childChat->id;
                        $childMessage->messageable_id = $parentMessage->messageable_type === 'App\Models\Guest' ? $childGuest->id : $copyHotel->id;
                        $childMessage->son_id = null;
                        $childMessage->exists = false;
                        $childMessage->save();
                    }
                } else {
                    $childMessage = $parentMessage->replicate();
                    $childMessage->chat_id = $childChat->id;
                    $childMessage->messageable_id = $parentMessage->messageable_type === 'App\Models\Guest' ? $childGuest->id : $copyHotel->id;
                    $childMessage->son_id = null;
                    $childMessage->save();
                    // Se guarda en el mensaje padre el mapeo hacia el mensaje hijo
                    $parentMessage->son_id = $childMessage->id;
                    $parentMessage->save();
                }
            }

            // Elimina los mensajes extra en el chat hijo que ya no existen en el padre
            $parentMessageIds = $parentChat->messages->pluck('son_id')->filter()->toArray();
            ChatMessage::where('chat_id', $childChat->id)
                      ->whereNotIn('id', $parentMessageIds)
                      ->delete();
            return true;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], __FUNCTION__);
        }
    }

    public function CleanRealStaysInCopyHotel($copyHotel){
        try {
            DB::beginTransaction();
            $stays = $copyHotel->stays()->where('trial',0)->get();
            foreach ($stays as $stay) {
                // delete stays notes
                $stay->notes()->delete();
                $guestsOfStay = $stay->guests()->get();
                //reset guests
                foreach ($guestsOfStay as $guest) {
                    // checkin reset
                    $guest->phone = null;
                    $guest->birthdate = null;
                    $guest->sex = null;
                    $guest->doc_type = null;
                    $guest->doc_num = null;
                    $guest->nationality = null;
                    $guest->address = null;
                    $guest->second_lastname = null;
                    $guest->responsible_adult = null;
                    $guest->kinship_relationship = null;
                    $guest->doc_support_number = null;
                    $guest->postal_code = null;
                    $guest->municipality = null;
                    $guest->country_address = null;
                    $guest->complete_checkin_data = 0;
                    $guest->checkin_email = null;
                    $guest->save();

                    //delete chats
                    $guest->chats()->where('stay_id',$stay->id)->delete();
                    //reset queries
                    $guestQueries = $guest->queries()->where('stay_id',$stay->id)->get();
                    foreach ($guestQueries as $query) {
                        $query->answered = 0;
                        $query->qualification = null;
                        $query->comment = null;
                        $query->attended = 0;
                        $query->visited = 0;
                        $query->responded_at = null;
                        $query->response_lang = null;
                        $query->save();
                    }

                    //delete guest notes
                    $guest->notes()->where('stay_id',$stay->id)->delete();


                }


            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], __FUNCTION__);
        }
    }


    public function UpdateChatSettingsInCopyHotel($originalHotel, $copyHotel){
        try {
            DB::beginTransaction();
            //settings chat
            //
            //
            $chatSettingsDefault = defaultChatSettings();
            $originalChatSettings = $originalHotel->chatSettings;
            $copyChatSettings = $copyHotel->chatSettings;
            if($originalChatSettings){
                //si existe el settings de chat en el hotel padre, se actualiza el settings de chat en el hotel hijo
                if($copyChatSettings){
                    //si existe el settings de chat en el hotel hijo, se actualiza el settings de chat en el hotel hijo
                    $copyChatSettings->fill($originalChatSettings->toArray());
                    $copyChatSettings->hotel_id = $copyHotel->id;
                    $copyChatSettings->save();
                    $sonsIds = [];

                    foreach ($originalChatSettings->languages as $language) {
                        if($language->pivot->son_id){
                            $sonsIds[] = $language->pivot->son_id;
                            $languageChild = ChatSettingLanguage::find($language->pivot->son_id);
                            if($languageChild){
                                $languageChild->language_id = $language->id;
                                $languageChild->chat_setting_id = $copyChatSettings->id;
                                $languageChild->son_id = null;
                                $languageChild->save();
                            }else{
                                $languageChild = new ChatSettingLanguage();
                                $languageChild->id = $language->pivot->son_id;
                                $languageChild->language_id = $language->id;
                                $languageChild->chat_setting_id = $copyChatSettings->id;
                                $languageChild->son_id = null;
                                $languageChild->exists = false;
                                $languageChild->save();
                            }
                        }else{
                            $chatSettingLanguage = new ChatSettingLanguage();
                            $chatSettingLanguage->chat_setting_id = $copyChatSettings->id;
                            $chatSettingLanguage->language_id = $language->id;
                            $chatSettingLanguage->son_id = null;
                            $chatSettingLanguage->save();

                            $chatSettingLanguageFather = ChatSettingLanguage::find($language->pivot->id);
                            $chatSettingLanguageFather->son_id = $chatSettingLanguage->id;
                            $chatSettingLanguageFather->save();
                            $sonsIds[] = $chatSettingLanguage->id;
                        }
                    }
                    $extraLanguagesInChildIds = $copyChatSettings->languages()->pluck('chat_setting_language.id')->toArray();
                    //obtengo los ids de los lenguajes que no estan en el array $sonsIds(es decir que no son hijos)
                    $idsToDelete = array_diff($extraLanguagesInChildIds, $sonsIds);
                    ChatSettingLanguage::whereIn('id', $idsToDelete)->delete();
                }else{
                    //si no existe el settings de chat en el hotel hijo, se crea el settings de chat en el hotel hijo
                    $copyChatSettings = $originalChatSettings->replicate();
                    $copyChatSettings->hotel_id = $copyHotel->id;
                    $copyChatSettings->save();
                    //este codigo es solo para cuando se crea el settings de chat en el hotel hijo
                    //por primera vez ya que luego no se puede eliminar chat_setting hijo

                    foreach ($originalChatSettings->languages as $language) {
                        $chatSettingLanguage = new ChatSettingLanguage();
                        $chatSettingLanguage->chat_setting_id = $copyChatSettings->id;
                        $chatSettingLanguage->language_id = $language->id;
                        $chatSettingLanguage->son_id = null;
                        $chatSettingLanguage->save();

                        $chatSettingLanguageFather = ChatSettingLanguage::find($language->pivot->id);
                        $chatSettingLanguageFather->son_id = $chatSettingLanguage->id;
                        $chatSettingLanguageFather->save();
                        // $copyChatSettings->languages()->syncWithoutDetaching($language->id);
                    }
                }
            }else{
                //si no existe el settings de chat en el hotel padre, pero si existe en el hotel hijo, se resetea al  settings default
                if($copyChatSettings){
                    $copyChatSettings->name = $chatSettingsDefault->name;
                    $copyChatSettings->show_guest = $chatSettingsDefault->show_guest;
                    $copyChatSettings->first_available_msg = $chatSettingsDefault->first_available_msg;
                    $copyChatSettings->first_available_show = $chatSettingsDefault->first_available_show;
                    $copyChatSettings->not_available_msg = $chatSettingsDefault->not_available_msg;
                    $copyChatSettings->not_available_show = $chatSettingsDefault->not_available_show;
                    $copyChatSettings->second_available_msg = $chatSettingsDefault->second_available_msg;
                    $copyChatSettings->second_available_show = $chatSettingsDefault->second_available_show;
                    $copyChatSettings->three_available_msg = $chatSettingsDefault->three_available_msg;
                    $copyChatSettings->three_available_show = $chatSettingsDefault->three_available_show;
                    $copyChatSettings->email_notify_new_message_to = $chatSettingsDefault->email_notify_new_message_to;
                    $copyChatSettings->email_notify_pending_chat_to = $chatSettingsDefault->email_notify_pending_chat_to;
                    $copyChatSettings->email_notify_not_answered_chat_to = $chatSettingsDefault->email_notify_not_answered_chat_to;
                    $copyChatSettings->hotel_id = $copyHotel->id;
                    $copyChatSettings->save();

                    $copyChatSettings->languages()->sync(collect($chatSettingsDefault->languages)->pluck('id')->toArray());

            //         // Sincroniza la relación en la estancia hija para que tenga exactamente estos huéspedes
            // $stayChild->guests()->sync(array_fill_keys($childGuestIds, ['chain_id' => $copyChain->id]));
                }
            }


            //settings chatHours
            $days = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
            $defaultChatHours = collect(defaultChatHours());
            $originalChatHours = $originalHotel->chatHours ?? []    ;
            $copyChatHours = $copyHotel->chatHours ?? [];

            if(count($originalChatHours) > 0){
                //si existe el settings de chatHours en el hotel padre, se actualiza el settings de chatHours en el hotel hijo
                if(count($copyChatHours) > 0){
                    //si existe el settings de chatHours en el hotel hijo, se actualiza el settings de chatHours en el hotel hijo
                    for($i = 0; $i < count($days); $i++){
                        $originalChatHour = $originalChatHours->where('day',$days[$i])->first();
                        $copyChatHour = $copyChatHours->where('day',$days[$i])->first();
                        if($originalChatHour){
                            $copyChatHour->fill($originalChatHour->toArray());
                            $copyChatHour->hotel_id = $copyHotel->id;
                            $copyChatHour->save();
                        }
                    }
                }else{
                    //si no existe el settings de chatHours en el hotel hijo, se crea el settings de chatHours en el hotel hijo
                    foreach ($originalChatHours as $originalChatHour) {
                        $copyChatHours = $originalChatHour->replicate();
                        $copyChatHours->hotel_id = $copyHotel->id;
                        $copyChatHours->save();
                    }
                }
            }else{
                //si no existe el settings de chatHours en el hotel padre, pero si existe en el hotel hijo, se resetea al  settings default
                if(count($copyChatHours) > 0){
                    for($i = 0; $i < count($days); $i++){
                        $defaultChatHour = $defaultChatHours->where('day',$days[$i])->first();
                        $copyChatHour = $copyChatHours->where('day',$days[$i])->first();
                        $copyChatHour->day = $defaultChatHour["day"];
                        $copyChatHour->active = $defaultChatHour["active"];
                        $copyChatHour->horary = $defaultChatHour["horary"];
                        $copyChatHour->save();
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], __FUNCTION__);
        }
    }

    public function UpdateCheckinSettingsInCopyHotel($originalHotel, $copyHotel){
        try {
            //settings checkin
            //
            //
            $default = CheckinSettingsDefaultEnum::defaultFieldsForm();
            $originalCheckinSettings = $originalHotel->checkinSettings;
            $copyCheckinSettings = $copyHotel->checkinSettings;
            if($originalCheckinSettings){
                //si existe el settings de checkin en el hotel padre, se actualiza el settings de checkin en el hotel hijo
                if($copyCheckinSettings){
                    //si existe el settings de checkin en el hotel hijo, se actualiza el settings de checkin en el hotel hijo
                    $copyCheckinSettings->fill($originalCheckinSettings->toArray());
                    $copyCheckinSettings->hotel_id = $copyHotel->id;
                    $copyCheckinSettings->save();
                }else{
                    //si no existe el settings de checkin en el hotel hijo, se crea el settings de checkin en el hotel hijo
                    $copyCheckinSettings = $originalCheckinSettings->replicate();
                    $copyCheckinSettings->hotel_id = $copyHotel->id;
                    $copyCheckinSettings->save();
                }
            }else{
                //si no existe el settings de checkin en el hotel padre, pero si existe en el hotel hijo, se resetea al  settings default
                if($copyCheckinSettings){
                    $copyCheckinSettings->succes_message = $default->succes_message;
                    $copyCheckinSettings->first_step = $default->first_step;
                    $copyCheckinSettings->second_step = $default->second_step;
                    $copyCheckinSettings->show_prestay_query = $default->show_prestay_query;
                    $copyCheckinSettings->hotel_id = $copyHotel->id;
                    $copyCheckinSettings->save();

                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], __FUNCTION__);
        }
    }

    public function UpdateQuerySettingsInCopyHotel($originalHotel, $copyHotel){
        try {
            //settings query
            //
            //
            $default = queriesTextDefault();
            $originalQuerySettings = $originalHotel->querySettings;
            $copyQuerySettings = $copyHotel->querySettings;
            if($originalQuerySettings){
                //si existe el settings de checkin en el hotel padre, se actualiza el settings de checkin en el hotel hijo
                if($copyQuerySettings){
                    //si existe el settings de checkin en el hotel hijo, se actualiza el settings de checkin en el hotel hijo
                    $copyQuerySettings->fill($originalQuerySettings->toArray());
                    $copyQuerySettings->hotel_id = $copyHotel->id;
                    $copyQuerySettings->save();
                }else{
                    //si no existe el settings de checkin en el hotel hijo, se crea el settings de checkin en el hotel hijo
                    $copyQuerySettings = $originalQuerySettings->replicate();
                    $copyQuerySettings->hotel_id = $copyHotel->id;
                    $copyQuerySettings->save();
                }
            }else{
                //si no existe el settings de checkin en el hotel padre, pero si existe en el hotel hijo, se resetea al  settings default
                if($copyQuerySettings){

                    $copyQuerySettings->pre_stay_activate = $default->pre_stay_activate;
                    $copyQuerySettings->pre_stay_thanks = $default->pre_stay_thanks;
                    $copyQuerySettings->pre_stay_comment = $default->pre_stay_comment;
                    $copyQuerySettings->in_stay_activate = $default->in_stay_activate;
                    $copyQuerySettings->in_stay_thanks_good = $default->in_stay_thanks_good;
                    $copyQuerySettings->in_stay_assessment_good_activate = $default->in_stay_assessment_good_activate;
                    $copyQuerySettings->in_stay_assessment_good = $default->in_stay_assessment_good;
                    $copyQuerySettings->in_stay_thanks_normal = $default->in_stay_thanks_normal;
                    $copyQuerySettings->in_stay_assessment_normal_activate = $default->in_stay_assessment_normal_activate;
                    $copyQuerySettings->in_stay_assessment_normal = $default->in_stay_assessment_normal;
                    $copyQuerySettings->in_stay_comment = $default->in_stay_comment;
                    $copyQuerySettings->post_stay_thanks_good = $default->post_stay_thanks_good;
                    $copyQuerySettings->post_stay_assessment_good_activate = $default->post_stay_assessment_good_activate;
                    $copyQuerySettings->post_stay_assessment_good = $default->post_stay_assessment_good;
                    $copyQuerySettings->post_stay_thanks_normal = $default->post_stay_thanks_normal;
                    $copyQuerySettings->post_stay_assessment_normal_activate = $default->post_stay_assessment_normal_activate;
                    $copyQuerySettings->post_stay_assessment_normal = $default->post_stay_assessment_normal;
                    $copyQuerySettings->post_stay_comment = $default->post_stay_comment;
                    $copyQuerySettings->notify_to_hoster = $default->notify_to_hoster;
                    $copyQuerySettings->email_notify_new_feedback_to = $default->email_notify_new_feedback_to;
                    $copyQuerySettings->email_notify_pending_feedback_to = $default->email_notify_pending_feedback_to;
                    $copyQuerySettings->hotel_id = $copyHotel->id;
                    $copyQuerySettings->save();

                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], __FUNCTION__);
        }
    }

    public function UpdateRequestSettingsInCopyHotel($originalHotel, $copyHotel){
        try {
            //settings request
            //
            //
            $default = requestSettingsDefault();
            $originalRequestSettings = $originalHotel->requestSettings;
             $copyRequestSettings = $copyHotel->requestSettings;
            if($originalRequestSettings){
                //si existe el settings de checkin en el hotel padre, se actualiza el settings de checkin en el hotel hijo
                if($copyRequestSettings){
                    //si existe el settings de checkin en el hotel hijo, se actualiza el settings de checkin en el hotel hijo
                    $copyRequestSettings->fill($originalRequestSettings->toArray());
                    $copyRequestSettings->hotel_id = $copyHotel->id;
                    $copyRequestSettings->save();
                }else{
                    //si no existe el settings de checkin en el hotel hijo, se crea el settings de checkin en el hotel hijo
                    $copyRequestSettings = $originalRequestSettings->replicate();
                    $copyRequestSettings->hotel_id = $copyHotel->id;
                    $copyRequestSettings->save();
                }
            }else{
                //si no existe el settings de checkin en el hotel padre, pero si existe en el hotel hijo, se resetea al  settings default
                if($copyRequestSettings){
                    $copyRequestSettings->msg_title = $default->msg_title;
                    $copyRequestSettings->msg_text = $default->msg_text;
                    $copyRequestSettings->otas_enabled = $default->otas_enabled;
                    $copyRequestSettings->request_to = $default->request_to;
                    $copyRequestSettings->in_stay_activate = $default->in_stay_activate;
                    $copyRequestSettings->in_stay_msg_title = $default->in_stay_msg_title;
                    $copyRequestSettings->in_stay_msg_text = $default->in_stay_msg_text;
                    $copyRequestSettings->in_stay_otas_enabled = $default->in_stay_otas_enabled;
                    $copyRequestSettings->hotel_id = $copyHotel->id;
                    $copyRequestSettings->save();

                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], __FUNCTION__);
        }
    }

    public function SyncGalleryImagesAndHotelImages($originalHotel, $copyHotel){
        try {
            //images gallery
            //
            //
            $originalGalleryImages = $originalHotel->gallery;

            foreach ($originalGalleryImages as $originalGalleryImage) {
                if ($originalGalleryImage->son_id) {
                    // Si ya se había creado un stay hijo previamente, se intenta recuperarlo
                    $galleryImageChild = ImageGallery::find($originalGalleryImage->son_id);

                    if ($galleryImageChild) {
                        // Si se encontró, se actualiza sus atributos copiando las imagenes padre
                        // (Ajusta los campos según corresponda)
                        $galleryImageChild->fill($originalGalleryImage->toArray());
                        $galleryImageChild->image_id = $copyHotel->id;
                        $galleryImageChild->son_id = null;
                        $galleryImageChild->save();
                    } else {
                        // Si la imagen hijo no existe (fue eliminado en el hotel hijo),
                        // se recrea usando el mismo id que estaba almacenado en el padre.
                        $galleryImageChild = $originalGalleryImage->replicate();
                        // Asigna manualmente el id guardado en el padre
                        $galleryImageChild->id = $originalGalleryImage->son_id;
                        $galleryImageChild->image_id = $copyHotel->id;
                        $galleryImageChild->son_id = null;
                        // Forzamos la inserción con el id específico.
                        $galleryImageChild->exists = false;
                        $galleryImageChild->save();
                    }
                } else {
                    // Si la imagen padre no tiene asignado un hijo, se crea uno nuevo (sin son_id)
                    $galleryImageChild = $originalGalleryImage->replicate();
                    $galleryImageChild->image_id = $copyHotel->id;
                    $galleryImageChild->son_id = null;
                    $galleryImageChild->save();
                    // Se actualiza la imagen padre para registrar el id dla imagen hijo creado
                    $originalGalleryImage->son_id = $galleryImageChild->id;
                    $originalGalleryImage->save();
                }
            }

            //images hotel
            //
            //
            $originalHotelImages = $originalHotel->images;
            $sonsIds = [];
            foreach ($originalHotelImages as $originalHotelImage) {
                if ($originalHotelImage->son_id) {
                    $sonsIds[] = $originalHotelImage->son_id;
                    // Si ya se había creado un stay hijo previamente, se intenta recuperarlo
                    $hotelImageChild = ImagesHotels::find($originalHotelImage->son_id);

                    if ($hotelImageChild) {
                        // Si se encontró, se actualiza sus atributos copiando las imagenes padre
                        // (Ajusta los campos según corresponda)
                        $hotelImageChild->fill($originalHotelImage->toArray());
                        $hotelImageChild->hotel_id = $copyHotel->id;
                        $hotelImageChild->son_id = null;
                        $hotelImageChild->save();
                    } else {
                        // Si la imagen hijo no existe (fue eliminado en el hotel hijo),
                        // se recrea usando el mismo id que estaba almacenado en el padre.
                        $hotelImageChild = $originalHotelImage->replicate();
                        // Asigna manualmente el id guardado en el padre
                        $hotelImageChild->id = $originalHotelImage->son_id;
                        $galleryImageChild->hotel_id = $copyHotel->id;
                        $hotelImageChild->son_id = null;
                        // Forzamos la inserción con el id específico.
                        $hotelImageChild->exists = false;
                        $hotelImageChild->save();
                    }
                } else {
                    // Si la imagen padre no tiene asignado un hijo, se crea uno nuevo (sin son_id)
                    $hotelImageChild = $originalHotelImage->replicate();
                    $hotelImageChild->hotel_id = $copyHotel->id;
                    $hotelImageChild->son_id = null;
                    $hotelImageChild->save();
                    // Se actualiza la imagen padre para registrar el id dla imagen hijo creado
                    $originalHotelImage->son_id = $hotelImageChild->id;
                    $originalHotelImage->save();
                    $sonsIds[] = $hotelImageChild->id;
                }
            }

            $extraImagesInChildIds = $copyHotel->images()->pluck('id')->toArray();
            //obtengo los ids de los lenguajes que no estan en el array $sonsIds(es decir que no son hijos)
            $idsToDelete = array_diff($extraImagesInChildIds, $sonsIds);
            ImagesHotels::whereIn('id', $idsToDelete)->delete();

        } catch (\Exception $e) {
            DB::rollBack();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], __FUNCTION__);
        }
    }

    public function CopyCustomization($HOTEL_ID_PARENT, $HOTEL_ID_CHILD, $CHAIN_ID_CHILD){
        if (empty($HOTEL_ID_PARENT) || empty($HOTEL_ID_CHILD) || empty($CHAIN_ID_CHILD)) {
            return;
        }

        $customizationParent = Customization::whereHas('chain', function($query) use ($HOTEL_ID_PARENT){
            $query->whereHas('hotel', function($query) use ($HOTEL_ID_PARENT){
                $query->where('id', $HOTEL_ID_PARENT);
            });
        })->first();

        if (!$customizationParent) {
            return;
        }

        $customizationChild = Customization::whereHas('chain', function($query) use ($CHAIN_ID_CHILD){
            $query->where('id', $CHAIN_ID_CHILD);
        })->first();

        try {
            DB::beginTransaction();

            if (!$customizationChild) {
                $customizationChild = $customizationParent->replicate();
                $customizationChild->chain_id = $CHAIN_ID_CHILD;
                $customizationChild->save();

                $customizationParent->son_id = $customizationChild->id;
                $customizationParent->save();
            } else {
                $customizationChild->colors = $customizationParent->colors;
                $customizationChild->logo = $customizationParent->logo;
                $customizationChild->name = $customizationParent->name;
                $customizationChild->type_header = $customizationParent->type_header;
                $customizationChild->tonality_header = $customizationParent->tonality_header;
                $customizationChild->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function SyncWifiNetworks($originalHotel, $copyHotel){
        try {
            //wifi networks

            $originalWifiNetworks = $originalHotel->wifiNetworks;
            $sonsIds = [];
            foreach ($originalWifiNetworks as $originalWifiNetwork) {
                if ($originalWifiNetwork->son_id) {
                    $sonsIds[] = $originalWifiNetwork->son_id;
                    // Si ya se había creado un stay hijo previamente, se intenta recuperarlo
                    $wifiNetworkChild = HotelWifiNetworks::find($originalWifiNetwork->son_id);

                    if ($wifiNetworkChild) {
                        // Si se encontró, se actualiza sus atributos copiando las imagenes padre
                        // (Ajusta los campos según corresponda)
                        $wifiNetworkChild->fill($originalWifiNetwork->toArray());
                        $wifiNetworkChild->hotel_id = $copyHotel->id;
                        $wifiNetworkChild->son_id = null;
                        $wifiNetworkChild->save();
                    } else {
                        // Si la imagen hijo no existe (fue eliminado en el hotel hijo),
                        // se recrea usando el mismo id que estaba almacenado en el padre.
                        $wifiNetworkChild = $originalWifiNetwork->replicate();
                        // Asigna manualmente el id guardado en el padre
                        $wifiNetworkChild->id = $originalWifiNetwork->son_id;
                        $wifiNetworkChild->hotel_id = $copyHotel->id;
                        $wifiNetworkChild->son_id = null;
                        // Forzamos la inserción con el id específico.
                        $wifiNetworkChild->exists = false;
                        $wifiNetworkChild->save();
                    }
                } else {
                    // Si la imagen padre no tiene asignado un hijo, se crea uno nuevo (sin son_id)
                    $wifiNetworkChild = $originalWifiNetwork->replicate();
                    $wifiNetworkChild->hotel_id = $copyHotel->id;
                    $wifiNetworkChild->son_id = null;
                    $wifiNetworkChild->save();
                    // Se actualiza la imagen padre para registrar el id dla imagen hijo creado
                    $originalWifiNetwork->son_id = $wifiNetworkChild->id;
                    $originalWifiNetwork->save();
                    $sonsIds[] = $wifiNetworkChild->id;
                }
            }

            $extraWifiNetworksInChildId = $copyHotel->wifiNetworks()->pluck('id')->toArray();
            //obtengo los ids de los lenguajes que no estan en el array $sonsIds(es decir que no son hijos)
            $idsToDelete = array_diff($extraWifiNetworksInChildId, $sonsIds);
            HotelWifiNetworks::whereIn('id', $idsToDelete)->delete();

        } catch (\Exception $e) {
            DB::rollBack();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], __FUNCTION__);
        }
    }
}
