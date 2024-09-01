<?php

namespace App\Services\Hoster\Stay;

use App\Jobs\Stay\DeleteStaySessionUser;
use App\Models\Stay;
use App\Services\QueryServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaySessionServices {
    
    public $queryService;

    function __construct(
        QueryServices $_QueryServices
    )
    {
        $this->queryService = $_QueryServices;
    }

    //sessions
    public function getSessions($stayId) {
        try {
            $stay = Stay::select('sessions')
                    ->where('id',$stayId)
                    ->first();
            return $stay->sessions;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function createSession($data) {
        try {
            $stayId = $data->stayId;
            $userColor = $data->userColor;
            $userEmail = $data->userEmail;
            $userName = $data->userName;
            
            $sessionArr = [
                    'userColor' => $userColor, 
                    'userEmail' => $userEmail, 
                    'userName' => $userName,
                    'updatedAt' => now(),
            ];
            //borrar posible job ya creado para esta session
            Log::info('createSession delete job '. $userEmail);
            DB::table('jobs')->where('payload', 'like', '%sendToUser' . $userEmail . '%')->delete();
            //si se renueva el job luego de 45min se eliminara la session
            Log::info('createSession create new job '. $userEmail);
            DeleteStaySessionUser::dispatch('sendToUser'.$userEmail, $userEmail, $stayId)->delay(now()->addMinutes(2));

            $stay = Stay::find($stayId);
            if($stay->sessions){
                $sessions = $stay->sessions ?? []; 
                // Verifica si el email ya existe en los arrays guardados
                foreach ($sessions as $session) {
                    if ($session['userEmail'] === $userEmail) {
                        return $stay->sessions;
                    }
                }
                // Si el email no existe, agrega el nuevo usuario a la lista
                $sessions[] = $sessionArr;
                $stay->sessions = $sessions;
                // $stay->save();
            }else{
                $stay->sessions = [$sessionArr];
            }
            //evitar actualizacion del updated_at
            $stay->timestamps = false;
            $stay->save();
            $stay->timestamps = true;
            return $stay->sessions;
        } catch (\Exception $e) {
            return $e;
        }
            
    }

    public function updateActionOrcreateSession($data) {
        try {
            $stayId = $data->stayId;
            $userColor = $data->userColor;
            $userEmail = $data->userEmail;
            $userName = $data->userName;
            
            $sessionArr = [
                    'userColor' => $userColor, 
                    'userEmail' => $userEmail, 
                    'userName' => $userName,
                    'updatedAt' => now(),
            ];
            //borrar posible job ya creado para esta session
            Log::info('updateOrcreateSession delete job '. $userEmail);
            DB::table('jobs')->where('payload', 'like', '%sendToUser' . $userEmail . '%')->delete();
            //si se renueva el job luego de 45min se eliminara la session
            Log::info('updateOrcreateSession create new job '. $userEmail);
            DeleteStaySessionUser::dispatch('sendToUser'.$userEmail, $userEmail, $stayId)->delay(now()->addMinutes(2));

            $stay = Stay::find($stayId);
            if($stay->sessions){
                $sessions = $stay->sessions ?? []; 
                $existsUser = false;
                Log::info('updateOrcreateSession guardada'. json_encode($sessions));
                // Verifica si el email ya existe en los arrays guardados
                foreach ($sessions as $key => $session) {
                    // Log::info('updateOrcreateSession session'. json_encode($session));
                    if ($session['userEmail'] === $userEmail) {
                        $existsUser = true;
                        // Log::info('updateOrcreateSession entro 1 foreach entro'. json_encode($session));
                        $sessions[$key] = $sessionArr;
                    }
                }
                Log::info('updateOrcreateSession $existsUser '. json_encode($existsUser));
                Log::info('updateOrcreateSession result'. json_encode($sessions));
                if($existsUser){
                    $stay->sessions = $sessions;
                }else{
                    Log::info('updateOrcreateSession $sessionArr '. json_encode($sessionArr));
                    Log::info('updateOrcreateSession antes de add arr '. json_encode($sessions));
                    $sessions[] = $sessionArr;
                    $stay->sessions = $sessions;
                    Log::info('updateOrcreateSession despues de add arr '. json_encode($sessions));
                }
            }else{
                $stay->sessions = [$sessionArr];
            }
            //evitar actualizacion del updated_at
            $stay->timestamps = false;
            $stay->save();
            $stay->timestamps = true;
            return $stay->sessions;
        } catch (\Exception $e) {
            return $e;
        }
            
    }

    public function deleteSession($stayId, $userEmail) {
        try {
            //borrar job ya creado para eliminar esta sesion
            Log::info('deleteSession delete created job');
            DB::table('jobs')->where('payload', 'like', '%sendToUser' . $userEmail . '%')->delete();
            $stay = Stay::find($stayId);
            // Log::info('deleteSession hotel_id:'. $stay->hotel_id);
            $sessions = $stay->sessions ?? [];
    
            // Filtra el array para eliminar el usuario con el email dado
            $filteredSessions = array_filter($sessions, function ($session) use ($userEmail) {
                return $session['userEmail'] !== $userEmail;
            });
    
            // Comprobar si el número de sesiones ha cambiado después del filtrado
            if (count($filteredSessions) === count($sessions)) return;
    
            // Si el array filtrado está vacío, establece sessions como null
            if (empty($filteredSessions)) {
                $stay->sessions = null;
            } else {
                $sessions = array_values($filteredSessions); // reindexa el array para asegurar la integridad de los índices
                $stay->sessions = $sessions;
            }
            
            //evitar actualizacion del updated_at
            $stay->timestamps = false;
            $stay->save();
            $stay->timestamps = true;
            Log::info('deleteSession to '. $userEmail);

            // Log::info('deleteSession $sessions:'. json_encode($sessions));
            sendEventPusher(
                'private-stay-sessions-hotel.' . $stay->hotel_id, 
                'App\Events\SessionsStayEvent', 
                [ 'stayId' => $stay->id, 'session' => $sessions]
            );
            return $stay->sessions;
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function findSessionByHotelAndEmail($hotelId, $userEmail) {
        try {
            return Stay::where('hotel_id', $hotelId)
                ->where('sessions','!=','')
                ->whereNotNull('sessions')
                ->whereJsonContains('sessions', ['userEmail' => $userEmail])
                ->first();

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
