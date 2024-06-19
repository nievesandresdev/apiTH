<?php

namespace App\Services;

use App\Models\Guest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


//servicios para agregar registros

class DataServices {

    public $guestService;

    function __construct(GuestService $_GuestService)
    {
        $this->guestService = $_GuestService;
    }

    public function AddColorAndAcronymToGuest () {
        try {
            $colors = $this->guestService->colors;
            $guests = Guest::select('id','name','email')->get();
            foreach($guests as $g){
                $name = $g->name;
                if(!$name)$name = $g->email;
                $acronym = $this->guestService->generateInitialsName($name);
                $color = $colors[array_rand($colors)];
                $g->acronym = $acronym;
                $g->color = $color;
                $g->save();
            }
            return 'completado';
        } catch (\Exception $e) {
            $e;
        }
    }
}