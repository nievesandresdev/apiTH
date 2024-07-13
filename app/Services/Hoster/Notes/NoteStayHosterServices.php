<?php

namespace App\Services\Hoster\Notes;

use App\Models\NoteStay;
use App\Models\Stay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NoteStayHosterServices {
    
    
    function __construct()
    {
        // $this->settings = $_QuerySetting;
    }

    public function create($stayId, $content){
        try {
            $stay = Stay::find($stayId);
            return $stay->notes()->create([
                'content' => $content,
            ]);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function update($noteId, $content){
        try {
            $note = NoteStay::find($noteId);
            $note->content = $content;
            $note->edited = 1;
            $note->save();
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function delete($noteId){
        try {
            $note = NoteStay::find($noteId);
            $note->delete();
        } catch (\Exception $e) {
            return $e;
        }
    }
    
    
}