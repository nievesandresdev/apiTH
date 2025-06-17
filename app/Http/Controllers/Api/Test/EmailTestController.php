<?php

namespace App\Http\Controllers\Api\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use App\Mail\Guest\{postCheckoutMail,prepareArrival,MsgStay};
use App\Services\MailService;
use App\Services\EmailTestService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class EmailTestController extends Controller
{
    protected $mailService;
    protected $emailTestService;

    public function __construct(
        MailService $mailService,
        EmailTestService $emailTestService
    )
    {
        $this->mailService = $mailService;
        $this->emailTestService = $emailTestService;
    }

     /* public function sendEmails(Request $request){
        App::setLocale($request->idioma);
        try {
            $hotel = (object)$request->hotel;
            if (isset($hotel->chat_settings)) {
                $hotel->chatSettings = (object)$hotel->chat_settings;
            }

            $guest = (object)[
                'email' => $request->email,
                'name' => $request->name,
                'lang_web' => $request->idioma,
                'off_email' => false
            ];

            // Define the order of emails
            $emailOrder = ['postCheckoutMail','checkout','postCheckin','prepareArrival','welcome'];

            // Filter and sort the requested emails according to the defined order
            $requestedEmails = array_intersect($emailOrder, $request->emails);

            foreach ($requestedEmails as $index => $emailType) {
                // Add delay between emails (except for the first one)
                if ($index > 0) {
                    sleep(2); // 2 seconds delay - adjust as needed
                    // Alternative: usleep(2000000); // 2 seconds in microseconds
                }

                switch ($emailType) {
                    case 'welcome':
                        $this->sendWelcomeEmail($hotel, $guest, $request);
                        break;
                    case 'prepareArrival':
                        $this->sendPrepareArrivalEmail($hotel, $guest, $request);
                        break;
                    case 'postCheckin':
                        $this->sendPostCheckinEmail($hotel, $guest, $request);
                        break;
                    case 'checkout':
                        $this->sendCheckoutEmail($hotel, $guest, $request);
                        break;
                    case 'postCheckoutMail':
                        $this->sendPostCheckoutEmail($hotel, $guest, $request);
                        break;
                }

                // Optional: Log progress
                \Log::info("Email sent: {$emailType} to {$guest->email}");
            }

            return bodyResponseRequest(EnumResponse::ACCEPTED, ['request' => $request->all()], 'sendEmails');
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e->getMessage(), ['error' => $e->getMessage()], 'sendEmails');
        }
    } */

    public function sendEmails(Request $request){
        App::setLocale($request->idioma);
        try {
            $hotel = (object)$request->hotel;
            if (isset($hotel->chat_settings)) {
                $hotel->chatSettings = (object)$hotel->chat_settings;
            }

            $guest = (object)[
                'email' => $request->email,
                'name' => $request->name,
                'lang_web' => $request->idioma,
                'off_email' => false
            ];

            // Define the order of emails
            $emailOrder = ['postCheckoutMail','checkout','postCheckin','prepareArrival','welcome'];

            // Filter and sort the requested emails according to the defined order
            $requestedEmails = array_intersect($emailOrder, $request->emails);

            foreach ($requestedEmails as $index => $emailType) {
                // Add delay between emails (except for the first one)
                if ($index > 0) {
                    sleep(3); // 2 seconds delay - adjust as needed
                    // Alternative: usleep(2000000); // 2 seconds in microseconds
                }

                switch ($emailType) {
                    case 'welcome':
                        $this->sendWelcomeEmail($hotel, $guest, $request);
                        break;
                    case 'prepareArrival':
                        $this->sendPrepareArrivalEmail($hotel, $guest, $request);
                        break;
                    case 'postCheckin':
                        $this->sendPostCheckinEmail($hotel, $guest, $request);
                        break;
                    case 'checkout':
                        $this->sendCheckoutEmail($hotel, $guest, $request);
                        break;
                    case 'postCheckoutMail':
                        $this->sendPostCheckoutEmail($hotel, $guest, $request);
                        break;
                }

                // Optional: Log progress
                Log::info("Email sent: {$emailType} to {$guest->email}");
            }

            return bodyResponseRequest(EnumResponse::ACCEPTED, ['request' => $request->all()], 'sendEmails');
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e->getMessage(), ['error' => $e->getMessage()], 'sendEmails');
        }
    }

    protected function sendWelcomeEmail($hotel, $guest, $request)
    {
        $dataEmail = $this->emailTestService->prepareWelcomeEmailData($hotel, $guest, $request);

        $this->mailService->sendEmail(
            new MsgStay('welcome', $hotel, $guest, $dataEmail, false, false),
            $guest->email
        );
    }

    protected function sendPrepareArrivalEmail($hotel, $guest, $request)
    {
        $dataEmail = $this->emailTestService->preparePrepareArrivalEmailData($hotel, $guest, $request);

        $this->mailService->sendEmail(
            new prepareArrival('prepare-arrival', $hotel, $guest, $dataEmail, true),
            $guest->email
        );
    }

    protected function sendPostCheckinEmail($hotel, $guest, $request)
    {
        $dataEmail = $this->emailTestService->preparePostCheckinEmailData($hotel, $guest, $request);

        $this->mailService->sendEmail(
            new MsgStay('postCheckin', $hotel, $guest, $dataEmail, false, false),
            $guest->email
        );
    }

    protected function sendCheckoutEmail($hotel, $guest, $request)
    {
        $dataEmail = $this->emailTestService->prepareCheckoutEmailData($hotel, $guest, $request);

        $this->mailService->sendEmail(
            new MsgStay('checkout', $hotel, $guest, $dataEmail, false, false),
            $guest->email
        );
    }

    protected function sendPostCheckoutEmail($hotel, $guest, $request)
    {
        $dataEmail = $this->emailTestService->preparePostCheckoutEmailData($hotel, $guest, $request);

        $this->mailService->sendEmail(
            new postCheckoutMail('checkout', $hotel, $guest, $dataEmail, true),
            $guest->email
        );
    }
}
