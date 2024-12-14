<?php

namespace App\Services\Apis;

use App\Models\HotelOta;
use App\Services\HttpClientService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;


class ApiHelpersServices {

    protected $KEY_API_REVIEW;
    protected $X_KEY_API;



    public function get_crosseling_hotel ($hotel) {
        $URL_BASE_API_HELPERS = config('app.url_base_helpers');
        //return $URL_BASE_API_HELPERS.'/place/getCrosselling';


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
        /* $headers = ['x-api-key' => $this->KEY_API_REVIEW]; */
        $response_request = $http_client_service->make_request('post', "$URL_BASE_API_HELPERS/hotels/getSummaryReviewsOtas", $body, [], 60);
        return [
            'response_request' => $response_request,
            'hotel' => $hotel,
            'cid' => $body,
            'url' => $URL_BASE_API_HELPERS.'/hotels/getSummaryReviewsOtas',

        ];
        // $response_request = null;
        $data = null;
        if (!isset($response_request['ok']) || !$response_request['ok']) {
            \Log::error($response_request['message']??$response_request);
            return;
        } else {
            $data = $response_request['data'] ?? null;
        }
        $data = $data['summaryReviews'] ?? null;
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
