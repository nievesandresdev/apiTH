<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Apis\ApiReviewServices;
use App\Utils\Enums\EnumResponse;
use App\Services\MailService;
use App\Mail\Platforms\LinkExternalPlatforms;

class ExternalPlatformsController extends Controller
{
    private $mailService;
    protected $api_review_service;
    public function __construct(
        MailService $_MailService,
        ApiReviewServices $_api_review_service
        )
    {
        $this->mailService = $_MailService;
        $this->api_review_service = $_api_review_service;

    }

    public function getDataOtas(Request $request){

        $hotel = $request->attributes->get('hotel');
        $summary_reviews = $this->api_review_service->getDataOta($hotel);

        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'otas' => $summary_reviews,
        ]);
    }
    public function requestChangeUrl(Request $request)
    {
        $hotel = $request->attributes->get('hotel');

        $this->mailService->sendEmail(new LinkExternalPlatforms($request->all(), $hotel), "info@thehoster.io");

        return bodyResponseRequest(EnumResponse::ACCEPTED, [
            'type' => request()->type,
            'data' => $request->url,
            'hotel' => $hotel,
            'initiator' => $request->init,
            'message' => 'Solicitud de cambio enviada.',
        ]);
    }

    //updateBulkOTAS
    public function updateBulkOTAS(Request $request)
    {
        $hotel = $request->attributes->get('hotel');
        $update = $this->api_review_service->updateBulkOTAS($hotel, $request->all());

        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'update' => $update,
        ]);

    }




}
