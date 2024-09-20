<?php

namespace App\Services\Hoster\Notes;

use App\Models\Guest;
use App\Models\NoteGuest;
use App\Services\Hoster\Stay\StaySessionServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NoteGuestHosterServices {

    protected $staySessionServices;
    
    function __construct(StaySessionServices $_StaySessionServices)
    {
        $this->staySessionServices = $_StaySessionServices;
    }

    public function create($stayId, $guestId, $content, $data){
        try {
            $this->staySessionServices->updateActionOrcreateSession($data);
            $guest = Guest::find($guestId);
            return $guest->notes()->create([
                'content' => $content,
                'stay_id' => $stayId,
            ]);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function update($noteId, $content, $data){
        try {
            $this->staySessionServices->updateActionOrcreateSession($data);
            $note = NoteGuest::find($noteId);
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
            $note = NoteGuest::find($noteId);
            $note->delete();
        } catch (\Exception $e) {
            return $e;
        }
    }
    
    
}