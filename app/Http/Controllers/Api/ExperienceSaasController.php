<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TypePlaces;

use App\Services\ExperienceService;

use App\Http\Resources\ExperienceResource;
use App\Http\Resources\ExperienceDetailResource;
use App\Http\Resources\ExperiencePaginateResource;
use Illuminate\Support\Str;
use App\Utils\Enums\EnumResponse;
use App\Services\CityService;

class ExperienceController extends Controller
{
    public $service;
    public $cityService;

    function __construct(
        ExperienceService $_ExperienceService,
        CityService $_CityService
    )
    {
        $this->service = $_ExperienceService;
        $this->cityService = $_CityService;
    }



}
