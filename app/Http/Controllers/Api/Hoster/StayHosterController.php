<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Notes\NoteStayHosterServices;
use App\Services\Hoster\Notes\NoteGuestHosterServices;
use App\Services\Hoster\Stay\StayHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Database\Seeders\run;

class StayHosterController extends Controller
{
    public $service;
    public $noteStayService;
    public $noteGuestService;

    function __construct(
        StayHosterServices $_StayHosterServices,
        NoteStayHosterServices $_NoteStayHosterServices,
        NoteGuestHosterServices $_NoteGuestHosterServices
    )
    {
        $this->service = $_StayHosterServices;
        $this->noteStayService = $_NoteStayHosterServices;
        $this->noteGuestService = $_NoteGuestHosterServices;
    }

    public function getAllByHotel(Request $request){
        try {
            
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAllByHotel($hotel, $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAllByHotel');
        }
    }

    public function statisticsByHotel(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->statisticsByHotel($hotel);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.statisticsByHotel');
        }
    }

    public function getdetailData(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getdetailData($request->stayId, $hotel);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getdetailData');
        }
    }
    
    public function updateData(Request $request){
        try {
            $model = $this->service->updateData($request->stayId, $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateData');
        }
    }

    public function getDefaultGuestIdAndSessions($stayId){
        try {
            $model = $this->service->getDefaultGuestIdAndSessions($stayId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getDefaultGuestIdAndSessions');
        }
    }


    //notes 
    
    public function getAllNotesByStay(Request $request){
        try {
            $model = $this->service->getAllNotesByStay($request->stayId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAllNotesByStay');
        }
    }

    public function createOrupdateStayNote(Request $request){
        try {
            $noteId = $request->noteId;
            $content = $request->content;
            $stayId = $request->stayId;
            $guestId = $request->guestId;
            $model = "Nota creada";

            if($noteId){
                $this->noteStayService->update($noteId, $content);
                $model = "Nota actualizada";
            }else{
                $this->noteStayService->create($stayId, $content);
            }
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.createOrupdateStayNote');
        }
    }

    public function deleteStayNote(Request $request){
        try {
            $noteId = $request->noteId;
            $model = $this->noteStayService->delete($noteId);
            // Nota eliminada.
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, 'Nota Eliminada');

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.deleteStayNote');
        }
    }

    public function createOrupdateGuestNote(Request $request){
        try {
            $noteId = $request->noteId;
            $content = $request->content;
            $stayId = $request->stayId;
            $guestId = $request->guestId;
            $model = "Nota creada";
            if($noteId){
                $this->noteGuestService->update($noteId, $content);
                $model = "Nota actualizada";
            }else{
                $this->noteGuestService->create($stayId, $guestId, $content);
            }
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.createOrupdateGuestNote');
        }
    }

    public function deleteGuestNote(Request $request){
        try {
            $noteId = $request->noteId;
            $model = $this->noteGuestService->delete($noteId);
            // Nota eliminada.
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, 'Nota Eliminada');

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.deleteGuestNote');
        }
    }


    //sessions
    public function getSessions(Request $request){
        try {
            $model = $this->service->getSessions($request->stayId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getSessions');
        }
    }
    
    public function createSession(Request $request){
        try {
            $model = $this->service->createSession($request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.createSession');
        }
    }

    public function deleteSession(Request $request){
        try {
            Log::info('deleteSession');
            $userEmail = $request->userEmail;
            $field = $request->field;
            $stayId = $request->stayId;

            $request->validate([
                'userEmail' => 'string',
                'field' => 'string',
                'stayId' => 'integer',
            ]);
            
            $model = $this->service->deleteSession($stayId, $userEmail);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.deleteSession');
        }
    }

    public function deleteSessionWithApiKey(Request $request){
        try {
            Log::info('deleteSessionWithApiKey');
            $userEmail = $request->query('userEmail');
            $stayId = $request->query('stayId');
            $request->validate([
                'xKeyApi' => 'string',
                'userEmail' => 'string',
                'field' => 'string',
                'stayId' => 'string',
            ]);

            $xKeyApi = $request->query('xKeyApi');
            $envApiKey = config('app.x_key_api');
            if($xKeyApi !== $envApiKey){
                    
            }
            $this->service->deleteSession($stayId, $userEmail);
            Log::info('session de hoster estancia eliminada con existo stayId:'. $stayId);
        } catch (\Exception $e) {
            Log::error('error al eliminar session de hoster estancia '. json_encode($e));
        }
    }

    public function deleteSessionByHotelAndEmail(Request $request){
        try {
            Log::info('deleteSessionByHotelAndEmail');
            $userEmail = $request->userEmail;
            $hotel = $request->attributes->get('hotel');
            $request->validate([
                'userEmail' => 'string|required',
            ]);
            
            //encontrar stayId mediante userEmail
            $model = null;
            $stay = $this->service->findSessionByHotelAndEmail($hotel->id, $userEmail);
            // Log para diagnosticar qué se está recibiendo
            Log::info('Type of $stay: ' . gettype($stay));
            Log::info('Content of $stay: ' . json_encode($stay));

            if($stay){
                Log::info('entro');
                $stayId = $stay->id;
                Log::info('llego?');
                $model = $this->service->deleteSession($stayId, $userEmail);
            }

            if(!$model || !$stay){
                $error = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $error);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.deleteSessionByHotelAndEmail');
        }
    }


    //guest
    public function getGuestListWithNoti(Request $request){
        try {
            $model = $this->service->getGuestListWithNoti($request->stayId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getGuestListWithNoti');
        }
    }

}
