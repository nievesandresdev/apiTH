<?php

namespace App\Services\Apis;

use App\Models\HotelOta;
use App\Services\HttpClientService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;


class ApiReviewServices {

    protected $KEY_API_REVIEW;

    public function __construct()
    {
        $this->KEY_API_REVIEW = config('app.key_api_review');
    }


    public function get_summary_reviews_otas ($hotel) {
        $URL_BASE_API_REVIEW = config('app.url_base_api_review');
        //return $URL_BASE_API_REVIEW.'/hotels/getSummaryReviewsOtas/';


        $url = $hotel->url_google;
        $cid = get_property_in_url($url, "cid");



        $params = [
            "googleMapCid" => $cid
        ];



        $http_client_service = new HttpClientService();
        $headers = ['x-api-key' => $this->KEY_API_REVIEW];
        $response_request = $http_client_service->make_request('get', "$URL_BASE_API_REVIEW/hotels/getSummaryReviewsOtas", $params, $headers, 60);
        /* return [
            'response_request' => $response_request,
            'hotel' => $hotel,
            'cid' => $params,
            'url' => $URL_BASE_API_REVIEW.'/hotels/getSummaryReviewsOtas',

        ]; */
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
