<?php

namespace App\Services;

use App\Models\Reward;
use Illuminate\Support\Str;
class RewardsServices {

    function getRewards($request, $modelHotel)
    {
        $rewards = Reward::where('hotel_id', $modelHotel->id)
            ->whereIn('type_rewards', ['referrals', 'referent'])
            ->get();

        return [
            'benefitSReferrals' => $rewards->where('type_rewards', 'referrals')->first(),
            'benefitReferent' => $rewards->where('type_rewards', 'referent')->first(),
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

        $modelHotel->update([
            'offer_benefits' => !$request->offer_benefits ? 0 : 1,
        ]);

        return $rewards;
    }

    function createCodeReferent($request, $modelHotel){
        $code = Str::random(7);
        return strtoupper($code);
    }



}
