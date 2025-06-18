<?php

namespace App\Services;

use App\Models\IntegrationPms;

class IntegrationPmsService {

    public function getPmswithFilters($modelHotel, $name) {
        $integrationPms = IntegrationPms::where('hotel_id', $modelHotel->id);
        if($name == '') {
            return $this->getIntegrationPms($modelHotel);
        }

        //filtrar por name_pms
        if ($name) {
            $integrationPms->where('name_pms','like', '%' . $name . '%');
        }

        $integrationPms = $integrationPms->get();

        //mapear los datos
        $integrationPms = $integrationPms->map(function ($item) {
            return $this->mapIntegrationPms($item);
        });
        return $integrationPms;
    }

    public function getIntegrationPms($modelHotel) {
        $integrationPms = $modelHotel->integrationPms()->get();

        //mapear los datos
        $integrationPms = $integrationPms->map(function ($item) {
            return $this->mapIntegrationPms($item);
        });
        return $integrationPms;

    }

    public function mapIntegrationPms($integrationPms) {
        return [
            'id' => $integrationPms->id,
            'name_pms' => $integrationPms->name_pms,
            'url_pms' => $integrationPms->url_pms,
            'icon_pms' => $this->getIconPms($integrationPms->name_pms),
            'with_url' => $integrationPms->with_url,
            'with_credentials' => $integrationPms->email_pms && $integrationPms->password_pms,
            'email_pms' => $integrationPms->email_pms,
            'password_pms' => $integrationPms->password_pms ? decrypt($integrationPms->password_pms) : null,
        ];
    }

    public function updateOrCreateCredentials($hotelModel, $data) {
        $integrationPms = IntegrationPms::updateOrCreate(
            ['id' => $data->pmsId],
            [
                'hotel_id' => $hotelModel->id,
                'email_pms' => $data->email,
                'password_pms' => encrypt($data->password),
            ]
        );

        return $integrationPms;
    }

    public function deleteCredentials($hotelModel, $data) {
        $integrationPms = IntegrationPms::where('hotel_id', $hotelModel->id)->where('id', $data->pmsId)->first();
        $integrationPms->email_pms = null;
        $integrationPms->password_pms = null;
        $integrationPms->save();
        return $integrationPms;
    }


    public function getIconPms($namePms) {
        $namePms = str_replace(' ', '_', strtolower($namePms));
        return $namePms;
    }

}


