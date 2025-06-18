<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\IntegrationPmsService;
use App\Utils\Enums\EnumResponse;

class IntegrationPmsController extends Controller
{
    protected $integrationPmsService;

    public function __construct(IntegrationPmsService $integrationPmsService)
    {
        $this->integrationPmsService = $integrationPmsService;
    }

    public function getPmswithFilters(Request $request, $name = '')
    {
        $hotelModel = $request->attributes->get('hotel');
        $integrationPms = $this->integrationPmsService->getPmswithFilters($hotelModel, $name);

        return bodyResponseRequest(EnumResponse::ACCEPTED, $integrationPms);
    }

    public function getIntegrationPms(Request $request)
    {
        $hotelModel = $request->attributes->get('hotel');
        $integrationPms = $this->integrationPmsService->getIntegrationPms($hotelModel);

        return bodyResponseRequest(EnumResponse::ACCEPTED, $integrationPms);

    }

    public function updateOrCreateCredentials(Request $request)
    {
        $hotelModel = $request->attributes->get('hotel');
        $integrationPms = $this->integrationPmsService->updateOrCreateCredentials($hotelModel, $request);

        return bodyResponseRequest(EnumResponse::ACCEPTED, $integrationPms);
    }

    public function deleteCredentials(Request $request)
    {
        $hotelModel = $request->attributes->get('hotel');
        $integrationPms = $this->integrationPmsService->deleteCredentials($hotelModel, $request);

        return bodyResponseRequest(EnumResponse::ACCEPTED, $integrationPms);
    }
}
