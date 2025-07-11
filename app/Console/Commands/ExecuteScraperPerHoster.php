<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\Apis\ApiReviewServices;
use App\Services\HotelService;

class ExecuteScraperPerHoster extends Command
{

    protected $signature = 'app:execute-scraper-per-hoster';

    protected $description = 'Ejecutar scrapers por hotel';

    public function __construct(
        ApiReviewServices $_api_review_services,
        HotelService $_api_hotel_services
    )
    {
        parent::__construct();
        $this->api_review_service = $_api_review_services;
        $this->api_hotel_services = $_api_hotel_services;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('max_execution_time', '72000');

        $hotels = $this->api_hotel_services->getHotelsSubscriptionActive();

        var_dump("Cantidad de hoteles a actualizar:".count($hotels));

        foreach ($hotels as $key => $hotel) {
            var_dump("Hotel: ".$hotel['name_origin']);
            ['slug' => $hotel_slug, 'id' => $hotel_id] = $hotel;
            
            $response = $this->api_review_service->sync_reviews_per_hotel($hotel);

        }
    }
}
