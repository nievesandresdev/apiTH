<?php

namespace App\Services\Apis;

use App\Models\HotelOta;
use App\Services\HttpClientService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use App\Services\NotificationDiscordService;


class ApiReviewServices {

    protected $KEY_API_REVIEW;
    protected $X_KEY_API;
    protected $notificationDiscordService;
    public function __construct(NotificationDiscordService $notificationDiscordService)
    {
        $this->KEY_API_REVIEW = config('app.key_api_review');
        $this->X_KEY_API = config('app.x_key_api');
        $this->notificationDiscordService = $notificationDiscordService;
    }


    public function get_summary_reviews_otas ($hotel) {
        $URL_BASE_API_REVIEW = config('app.url_base_api_review');
        //return $URL_BASE_API_REVIEW.'/hotels/getSummaryReviewsOtas/';


        $code = $hotel->code;

        $params = [
            "googleMapCid" => $code
        ];

        $http_client_service = new HttpClientService();
        $headers = ['x-api-key' => $this->KEY_API_REVIEW];
        $url = "$URL_BASE_API_REVIEW/hotels/getSummaryReviewsOtas";
        $response_request = $http_client_service->make_request('get', $url, $params, $headers, 60);
        //
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

    public function getDataOta($hotel) {
        $URL_BASE_API_REVIEW = config('app.url_base_api_review');
        //return $URL_BASE_API_REVIEW.'/hotels/getSummaryReviewsOtas/';


        $url = $hotel->url_google;
        $cid = get_property_in_url($url, "cid");

        $params = [
            "googleMapCid" => $cid
        ];


        $http_client_service = new HttpClientService();
        $headers = ['x-api-key' => $this->X_KEY_API];
        $response_request = $http_client_service->make_request('GET', "$URL_BASE_API_REVIEW/hotelOtas/getByParams", $params, $headers, 60);

        /* return [
            'params' => $params,
            'url' => $URL_BASE_API_REVIEW.'/hotelOtas/getByParams',
            'key' => $this->X_KEY_API,
            'response' => $response_request
        ]; */

        // $response_request = null;
        $data = null;
        if (!isset($response_request['ok']) || !$response_request['ok']) {
            \Log::error($response_request['message']??$response_request);
            return;
        } else {
            $data = $response_request ?? null;
        }
        $data = $data['data'] ?? null;

        return $this->convert_keys_to_snake_case($data);

    }

    public function findDataOta($hotel,$ota) {
        $URL_BASE_API_REVIEW = config('app.url_base_api_review');
        //return $URL_BASE_API_REVIEW.'/hotels/getSummaryReviewsOtas/';


        $code = $hotel->code;

        $params = [
            "ota" => $ota,
            "googleMapCid" => $code
        ];


        $http_client_service = new HttpClientService();
        $headers = ['x-api-key' => $this->X_KEY_API];
        $response_request = $http_client_service->make_request('GET', "$URL_BASE_API_REVIEW/hotels/findByParams", $params, $headers, 60);

        /* return [
            'params' => $params,
            'url' => $URL_BASE_API_REVIEW.'/hotelOtas/getByParams',
            'key' => $this->X_KEY_API,
            'response' => $response_request
        ]; */

        // $response_request = null;
        $data = null;
        if (!isset($response_request['ok']) || !$response_request['ok']) {
            \Log::error($response_request['message']??$response_request);
            return;
        } else {
            $data = $response_request ?? null;
        }
        $data = $data['data'] ?? null;

        return $this->convert_keys_to_snake_case($data);

    }

    public function updateBulkOTAS($hotel,$request){
        $URL_BASE_API_REVIEW = config('app.url_base_api_review');



        $url = $hotel->url_google;
        $cid = get_property_in_url($url, "cid");

        $params = [
            "googleMapCid" => $cid,
            "urls" => $request

        ];

        /* return [
            'params' => $params,
            'url' => $URL_BASE_API_REVIEW.'/hotelOtas/updateBulk',
            'key' => $this->KEY_API_REVIEW
        ]; */

        //return $params;

        $http_client_service = new HttpClientService();
        $headers = ['x-api-key' => $this->X_KEY_API];
        $response_request = $http_client_service->make_request('POST', "$URL_BASE_API_REVIEW/hotelOtas/updateBulk", $params, $headers, 60);

        return [
            'params' => $params,
            'url' => $URL_BASE_API_REVIEW.'/hotelOtas/updateBulk',
            'respojnse' => $response_request,
            'headers' => $headers
        ];

        $data = null;
        if (!isset($response_request['ok']) || !$response_request['ok']) {
            \Log::error($response_request['message']??$response_request);
            return;
        } else {
            $data = $response_request ?? null;
        }
        $data = $data['data'] ?? null;

        return $this->convert_keys_to_snake_case($data);
    }

    public function syncReviews($hotel, $otas) {
        $body = [
            'googleMapCid' => $hotel->code,
            'validOtas' => $otas
        ];

        $URL_BASE_API_REVIEW = config('app.url_base_api_review');
        $http_client_service = new HttpClientService();
        $headers = ['x-api-key' => $this->X_KEY_API];
        $response_request = $http_client_service->make_request('POST', "$URL_BASE_API_REVIEW/reviews/syncBulk", $body, $headers, 60);

        $data = null;
        if (!isset($response_request['ok']) || !$response_request['ok']) {
            // \Log::error($response_request['message']??$response_request);
            $this->notificationDiscordService->sendMessage("Error Sync Reviews", "Error Sync Reviews");
            return;
        } else {
            \Log::info("Sync Reviews");
            $this->notificationDiscordService->sendMessage("Success Sync Reviews", "Success Sync Reviews");
            $data = $response_request ?? null;
        }
        return $data;
    }

    public function leakedReviewsStoreBulkByOta($hotel,$ota) {
        $body = [
            'googleMapCid' => $hotel->code,
            'ota' => $ota
        ];

        $URL_BASE_API_REVIEW = config('app.url_base_api_review');
        $http_client_service = new HttpClientService();
        $headers = ['x-api-key' => $this->X_KEY_API];
        $response_request = $http_client_service->make_request('POST', "$URL_BASE_API_REVIEW/leakedReviews/storeBulkByOta", $body, $headers, 60);

        $data = null;
        if (isset($response_request['ok']) && $response_request['ok']) {
            var_dump('todo ok en leakedReviewsStoreBulkByOta '.$ota);
            $this->notificationDiscordService->sendMessage("Success Leaked Reviews Store Bulk By Ota $ota", "Success Leaked Reviews Store Bulk By Ota $ota");
            \Log::info("Success Leaked Reviews Store Bulk By Ota $ota");
            return;
        } else {
            var_dump('error en leakedReviewsStoreBulkByOta');
            $this->notificationDiscordService->sendMessage("Error Leaked Reviews Store Bulk By Ota $ota", "Error Leaked Reviews Store Bulk By Ota $ota");
            \Log::error("Error Leaked Reviews Store Bulk By Ota $ota");
            $data = $response_request ?? null;
        }
        return $data;
    }

    public function translateReviewsByOta($hotel,$ota) {
        $body = [
            'googleMapCid' => $hotel->code,
            'ota' => $ota,
            'hotelName' => $hotel->name
        ];

        $URL_BASE_API_REVIEW = config('app.url_base_api_review');
        $http_client_service = new HttpClientService();
        $headers = ['x-api-key' => $this->X_KEY_API];
        $response_request = $http_client_service->make_request('POST', "$URL_BASE_API_REVIEW/translateAndResponse/storeLastMonthByOta", $body, $headers, 60);
        // \Log::info($response_request);
        $data = null;
        if (isset($response_request['ok']) && $response_request['ok']) {
            var_dump('todo ok en translateReviewsByOta '.$ota);
            $this->notificationDiscordService->sendMessage("Success Translate Reviews $ota", "Success Translate Reviews $ota");
            \Log::info("Success Translate Reviews $ota");
            return;
        } else {
            var_dump('error en translateReviewsByOta '.$ota);
            $this->notificationDiscordService->sendMessage("Error Translate Reviews $ota", "Error Translate Reviews $ota");
            \Log::error("Error Translate Reviews $ota");
            $data = $response_request ?? null;
        }
        return $data;
    }

    public function updateReviews($hotel) {
        $OTAS = ['BOOKING', 'EXPEDIA', 'TRIPADVISOR', 'GOOGLE', 'AIRBNB'];
        foreach ($OTAS as $ota) {
            $this->syncReviews($hotel, [$ota]);
            $this->leakedReviewsStoreBulkByOta($hotel, $ota);
            $this->translateReviewsByOta($hotel, $ota);
        }

    }

}
