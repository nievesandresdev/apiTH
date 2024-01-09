<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;
use App\Services\StayService;
class StayController extends Controller
{
    public $service;

    function __construct(
        StayService $_StayService
    )
    {
        $this->service = $_StayService;
    }

    public function findStayByParams (Request $request) {
       return 'hola que hace';
    }

   

}
