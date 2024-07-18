<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Notes\NoteStayHosterServices;
use App\Services\Hoster\Notes\NoteGuestHosterServices;
use App\Services\Hoster\Stay\StayHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

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
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

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
                $model =$this->noteGuestService->update($noteId, $content);
                // $model = "Nota actualizada";
            }else{
                $model =$this->noteGuestService->create($stayId, $guestId, $content);
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
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.deleteGuestNote');
        }
    }


    //sessions

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
            $model = $this->service->deleteSession($request);
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
    
}
