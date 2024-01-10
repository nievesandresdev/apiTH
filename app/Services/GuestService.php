<?php

namespace App\Services;

use App\Models\Guest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class GuestService {

    public function findById($id)
    {
        try {
            return $guest = Guest::find($id);
        } catch (\Exception $e) {
            return $e;
        }
    }
}