<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\WorkPosition;
use App\Utils\Enums\EnumResponse;

class WorkPositionController extends Controller
{
    public function getAllWorkPosition()
    {
        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'work_positions' => WorkPosition::active()->get()
        ]);
    }
}
