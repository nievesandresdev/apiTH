<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Users\UserServices;
use App\Utils\Enums\EnumResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    protected $userServices;

    public function __construct(UserServices $userServices)
    {
        $this->userServices = $userServices;
    }

    public function store(){

        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'message' => 'Creado con éxito',
            'user' => request()->all()
        ]);

    }
}
