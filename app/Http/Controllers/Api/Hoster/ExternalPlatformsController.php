<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use App\Services\MailService;
use App\Mail\Platforms\LinkExternalPlatforms;

class ExternalPlatformsController extends Controller
{
    private $mailService;
    public function __construct(MailService $_MailService)
    {
        $this->mailService = $_MailService;
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

}
