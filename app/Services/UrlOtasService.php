<?php

namespace App\Services;

use App\Services\Apis\ApiReviewServices;

class UrlOtasService
{
    protected $apiReviewService;

    public function __construct(
        ApiReviewServices $apiReviewService
    )
    {
        $this->apiReviewService = $apiReviewService;
    }

    /**
     * Obtener la URL de Google.
     *
     * @param $hotel
     * @return string
     */
    public function google($hotel)
    {
        $findByOta = $this->apiReviewService->findDataOta($hotel, 'GOOGLE');

        if ($findByOta === null) {
            return '#'; // Google no existe
        }

        $googlePlaceId = $findByOta['hotel']['place_id'] ?? null;

        return $googlePlaceId
            ? "https://search.google.com/local/writereview?placeid={$googlePlaceId}"
            : '#';
    }

    /**
     * Obtener la URL de Tripadvisor.
     *
     * @param $hotel
     * @return string
     */
    public function tripadvisor($hotel)
    {
        $review = $this->apiReviewService->getDataOta($hotel);

        if ($review === null) {
            return '#'; // Tripadvisor no existe
        }

        $tripadvisorReview = array_filter($review['otas'], function ($ota) {
            return isset($ota['ota']) && strtoupper($ota['ota']) === 'TRIPADVISOR';
        });

        return !empty($tripadvisorReview) ? reset($tripadvisorReview)['url'] : '#';
    }

    /**
     * Obtener las URLs de Booking (normal y responsive).
     *
     * @return array
     */
    public function booking()
    {
        return [
            'url' => 'https://secure.booking.com/reviewtimeline.html', // Normal
            'url_responsive' => 'https://secure.booking.com/reviewmanage.html', // Responsive
        ];
    }

    public function getOtasWithUrls($hotel, $otasEnabled)
    {
        $urls = [
            'google' => $this->google($hotel),
            'tripadvisor' => $this->tripadvisor($hotel),
            'booking' => $this->booking(),
        ];

        $filteredOtas = array_filter($otasEnabled, function ($enabled, $key) {
            return $enabled && in_array($key, ['booking', 'google', 'tripadvisor']);
        }, ARRAY_FILTER_USE_BOTH);

        $otasWithUrls = [];
        foreach ($filteredOtas as $key => $enabled) {
            $otasWithUrls[$key] = [
                'name' => ucfirst($key),
                'url' => is_array($urls[$key]) ? $urls[$key]['url'] : ($urls[$key] ?? '#'),
                'url_responsive' => is_array($urls[$key]) ? $urls[$key]['url_responsive'] : null, // Solo para Booking
            ];
        }

        return $otasWithUrls;
    }

}
