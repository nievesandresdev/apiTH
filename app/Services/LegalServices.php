<?php

namespace App\Services;

use App\Models\Legal\LegalGeneral;
use App\Models\Legal\PolicyLegals;

class LegalServices {

    public function getGeneralLegal($hotel)
    {
        return $hotel->generalLegal;
    }

    public function getPolicyLegal($hotel, $perPage, $page) {
        return $hotel->policies()->paginate($perPage, ['*'], 'page', $page);
    }

    public function getCountPoliciesByHotel($hotel) {
        return $hotel->policies()->count();
    }

    public function storeOrUpdateLegalGeneral($hotel, $data)
    {
        $legalGeneral = LegalGeneral::updateOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'name' => $data['name'],
                'address' => $data['address'],
                'nif' => $data['nif'],
                'email' => $data['email'],
                'protection' => $data['protection'],
                'email_protection' => $data['email_protection'],
            ]
        );

        return $legalGeneral;
    }

    public function storeLegalPolicies($hotel, $data)
    {
        $policy = new PolicyLegals();
        $policy->hotel_id = $hotel->id;
        $policy->title = $data['title'];
        $policy->description = $data['description'];
        $policy->penalization = $data['penalization'];
        $policy->penalization_details =  $data['penalization'] == 1 ? $data['penalizationDetails'] : null;
        $policy->save();

        return $policy;
    }

    public function updateLegalPolicies($data)
    {
        $policy = PolicyLegals::find($data['id']);
        $policy->title = $data['title'];
        $policy->description = $data['description'];
        $policy->penalization = $data['penalization'];
        $policy->penalization_details =  $data['penalization'] == 1 ? $data['penalizationDetails'] : null;
        $policy->save();

        return $policy;
    }

    public function deleteLegalPolicies($data)
    {
        $policy = PolicyLegals::find($data['id']);
        $policy->del = 1;
        $policy->save();

        return $policy;
    }

     //WEBAPP METHODS
    public function getNormsByHotel($hotelId)
    {
        return PolicyLegals::where('hotel_id',$hotelId)
                            ->where('del',0)
                            ->get();
    }
}
