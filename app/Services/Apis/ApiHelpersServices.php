<?php

namespace App\Services\Apis;

use App\Services\HttpClientService;

class ApiHelpersServices {

    public function get_crosseling_hotel ($hotel) {
        $URL_BASE_API_HELPERS = config('app.url_base_helpers');

        $body = [
            "hotel" => [
                "id" => $hotel->id,
                "name" => $hotel->name,
                "zone" => $hotel->zone,
                "latitude" => $hotel->latitude,
                "longitude" => $hotel->longitude,
            ]
        ];


        $http_client_service = new HttpClientService();
        $response_request = $http_client_service->make_request('post', "$URL_BASE_API_HELPERS/place/getCrosselling", $body, [], 60);

        $data = null;
        if (!isset($response_request['ok']) || !$response_request['ok']) {
            \Log::error($response_request['message']??$response_request);
            return;
        } else {
            $data = $response_request['data'] ?? null;
        }
        return $this->convert_keys_to_snake_case($data);

    }

    public function convert_keys_to_snake_case ($array) {
        $result = [];
        if (empty($array)) return;
        foreach ($array as $key => $value) {
            $newKey = \Str::snake($key);

            if (is_array($value)) {
                $value = $this->convert_keys_to_snake_case($value);
            }

            $result[$newKey] = $value;
        }

        return $result;
    }




}
