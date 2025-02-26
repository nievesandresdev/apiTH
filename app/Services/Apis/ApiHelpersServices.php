<?php

namespace App\Services\Apis;

use App\Services\HttpClientService;
use Illuminate\Support\Facades\Log;

class ApiHelpersServices {

    public function get_crosseling_hotel ($hotel) {
        try{
            $URL_BASE_API_HELPERS = config('app.url_base_helpers');

            $body = [
                "hotel" => [
                    "id" => $hotel->id,
                    "name" => $hotel->name,
                    "zone" => $hotel->city_id,
                    "latitude" => $hotel->latitude,
                    "longitude" => $hotel->longitude,
                ]
            ];

            dd($body,$URL_BASE_API_HELPERS."place/getCrosselling");


            $http_client_service = new HttpClientService();
            $response_request = $http_client_service->make_request('POST', $URL_BASE_API_HELPERS."place/getCrosselling", $body, [], 60);

            $data = null;
            if (!isset($response_request['ok']) || !$response_request['ok']) {
                Log::error($response_request['message']??$response_request);
                return;
            } else {
                $data = $response_request['data'] ?? null;
            }
            return $this->convert_keys_to_snake_case($data);
        } catch (\Exception $e) {
            Log::error('Error service get_crosseling_hotel: ' . $e->getMessage());
            $e;
        }

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
