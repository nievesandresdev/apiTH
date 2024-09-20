<?php

namespace App\Services\Hoster\Notes;

use App\Models\NoteStay;
use App\Models\Stay;
use App\Services\Hoster\Stay\StaySessionServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NoteStayHosterServices {
    
    protected $staySessionServices;
    
    function __construct(StaySessionServices $_StaySessionServices)
    {
        $this->staySessionServices = $_StaySessionServices;
    }

    public function create($stayId, $content, $data){
        try {
            $this->staySessionServices->updateActionOrcreateSession($data);
            $stay = Stay::find($stayId);
            return $stay->notes()->create([
                'content' => $content,
            ]);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function update($noteId, $content, $data){
        try {
            $this->staySessionServices->updateActionOrcreateSession($data);
            $note = NoteStay::find($noteId);
            $note->content = $content;
            $note->edited = 1;
            $note->save();
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function delete($noteId, $data){
        try {
            $this->staySessionServices->updateActionOrcreateSession($data);
            $note = NoteStay::find($noteId);
            $note->delete();
        } catch (\Exception $e) {
            return $e;
        }
    }
    
    
}