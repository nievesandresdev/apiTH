<?php

namespace App\Services;

use App\Models\{Reward, RewardStay};
use Illuminate\Support\Str;
use App\Services\MailService;
use App\Mail\User\RewardsEmail;
use Illuminate\Support\Facades\Log;
use App\Models\Hotel;
use Illuminate\Support\Facades\App;
class RewardsServices {

    public $mailService;

    function __construct(
        MailService $_MailService
    ){
        $this->mailService = $_MailService;
    }

    function getRewards($request, $modelHotel)
    {
        return [
            'benefitSReferrals' => Reward::where('hotel_id', $modelHotel->id)
                ->where('type_rewards', 'referrals')
                ->first(),
            'benefitReferent' => Reward::where('hotel_id', $modelHotel->id)
                ->where('type_rewards', 'referent')
                ->first(),
        ];
    }


    function storeOrUpdateRewards($request, $modelHotel)
    {
        $rewards = [];

        // Validar y procesar benefitSReferrals
        if (!empty($request->benefitSReferrals)) {
            $rewardReferrals = Reward::updateOrCreate(
                [
                    'hotel_id' => $modelHotel->id,
                    'type_rewards' => 'referrals',
                ],
                [
                    'amount' => str_replace(',', '.', $request->benefitSReferrals['amount']),
                    'code' => $request->benefitSReferrals['code'],
                    'description' => $request->benefitSReferrals['description'],
                    'type_discount' => $request->benefitSReferrals['type_discount'],
                    'url' => $request->benefitSReferrals['url'],
                    'enabled_url' => $request->benefitSReferrals['enabled_url'],
                ]
            );
            $rewards[] = $rewardReferrals;
        }

        // Validar y procesar benefitReferent
        if (!empty($request->benefitReferent)) {
            $rewardReferent = Reward::updateOrCreate(
                [
                    'hotel_id' => $modelHotel->id,
                    'type_rewards' => 'referent',
                ],
                [
                    'amount' => str_replace(',', '.', $request->benefitReferent['amount']),
                    'code' => $request->benefitReferent['code'],
                    'description' => $request->benefitReferent['description'],
                    'type_discount' => $request->benefitReferent['type_discount'],
                    'url' => $request->benefitReferent['url'],
                    'enabled_url' => $request->benefitReferent['enabled_url'],
                ]
            );
            $rewards[] = $rewardReferent;
        }

        /* $modelHotel->update([
            'offer_benefits' => !$request->offer_benefits ? 0 : 1,
        ]); */
        $hotel = Hotel::where('id', $modelHotel->id)->first();
        $hotel->update([
            'offer_benefits' => !$request->offer_benefits ? 0 : 1,
        ]);

        return $rewards;
    }

    function createCodeReferent($request, $modelHotel){

        $code = Str::random(7);

        $rewardStay = RewardStay::create([
            'code' => strtoupper($code),
            'hotel_id' => $modelHotel->id,
            'stay_id' => $request->stay_id,
            'guest_id' => $request->guest_id,
            'reward_id' => $request->reward_id,
        ]);

        return $rewardStay->load('reward');
    }

    function sendEmailReferent($rewardStay){

        $chainSubdomain = $rewardStay->hotel->chain->subdomain;
        $urlWebapp = buildUrlWebApp($chainSubdomain, $rewardStay->hotel->subdomain);
        $urlFooterEmail = buildUrlWebApp($chainSubdomain, $rewardStay->hotel->subdomain,'no-notificacion',"e={$rewardStay->stay_id}&g={$rewardStay->guest_id}");

        $data = [
            'webappChatLink' => buildUrlWebApp($rewardStay->hotel->subdomain, $rewardStay->hotel->subdomain,'chat'),
            'urlQr' => generateQr($rewardStay->hotel->subdomain, $urlWebapp),
            'urlPrivacy' => buildUrlWebApp($chainSubdomain, $rewardStay->hotel->subdomain,'privacidad',"e={$rewardStay->stay_id}&g={$rewardStay->guest_id}&email=true&lang={$rewardStay->guest->lang_web}"),
            'urlFooterEmail' => $urlFooterEmail
        ];

        $communication = $rewardStay->hotel->hotelCommunications->firstWhere('type', 'email');
        $shouldSend = !$communication || $communication->referent_email;

        if($shouldSend){
            Log::info('sendEmailReferentSErvices', ['data' => $data]);
            App::setLocale($rewardStay->guest->lang_web);
            $this->mailService->sendEmail(new RewardsEmail($rewardStay->hotel, $rewardStay, $data), $rewardStay->guest->email);
        }else{
            Log::info('sendEmailReferentSErvices no se envia', ['data' => $data]);
        }

    }

}
