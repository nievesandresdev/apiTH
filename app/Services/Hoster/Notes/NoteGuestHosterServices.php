<?php

namespace App\Services\Hoster\Notes;

use App\Models\Guest;
use App\Models\NoteGuest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NoteGuestHosterServices {
    
    
    function __construct()
    {
        // $this->settings = $_QuerySetting;
    }

    public function create($stayId, $guestId, $content){
        try {
            $guest = Guest::find($guestId);
            return $guest->notes()->create([
                'content' => $content,
                'stay_id' => $stayId,
            ]);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function update($noteId, $content){
        try {
            $note = NoteGuest::find($noteId);
            $note->content = $content;
            $note->edited = 1;
            $note->save();
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function delete($noteId){
        try {
            $note = NoteGuest::find($noteId);
            $note->delete();
        } catch (\Exception $e) {
            return $e;
        }
    }
    
    
}