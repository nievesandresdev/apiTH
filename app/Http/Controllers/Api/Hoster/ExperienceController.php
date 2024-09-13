<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Products;
use App\Models\ToggleProduct;

use App\Services\Hoster\ExperienceService;

use App\Http\Resources\ExperienceResource;
use App\Http\Resources\ExperienceDetailResource;

use Illuminate\Support\Str;
use App\Utils\Enums\EnumResponse;
use App\Services\CityService;

use App\Jobs\TranslateModelJob;

use App\Http\Resources\ExperiencePaginateResource;

class ExperienceController extends Controller
{
    public $service;
    public $cityService;

    function __construct(
        ExperienceService $_ExperienceService,
        CityService $_CityService
    )
    {
        $this->service = $_ExperienceService;
        $this->cityService = $_CityService;
    }

    public function getAll (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');

            $lengthAExpFeatured = 12;
            $hotelId = $hotelModel->id;
            $priceMin = $request->price_min ?? null;
            $priceMax = $request->price_max ?? null;
            $priceMax = $request->price_max ?? null;
            $priceMax = $request->price_max ?? null;
            $search = $request->search ?? null;
            $cityName = $request->city ?? $hotelModel->zone;       
            $featured = $request->featured && $request->featured != 'false' && $request->featured != '0';
            $all_cities = boolval($request->all_cities) ?? false;
            $city_latitude = $request->city_latitude;
            $city_longitude = $request->city_longitude;
            $one_exp_id = $request->one_exp_id ?? null;
            $visibility = $request->visibility ?? null;
            $duration = [];
            if (!empty($request->duration)) {
                $duration = gettype($request->duration) == 'string' ? json_decode($request->duration, true) : $request->duration;
            }

            //crear array de ciudades para la consulta
            $citySlug = Str::slug($hotelModel->zone);
            $cityModel  = $this->cityService->findByParams([ 'slug' => $citySlug]);

            $dataFilter = [
                'city' => $cityName,
                'all_cities' => $all_cities,
                'search' => $search,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'duration' => $duration,
                'score' => $request->score ?? [],
                'free_cancelation' => $request->free_cancelation ?? null,
                'featured' => $featured,
                'one_exp_id' => $one_exp_id,
                'visibility' => $visibility,
            ];

            $queryExperiences = $this->service->queryGetAll($request, $hotelModel, $dataFilter, $cityModel);
            // return $queryExperiences->count();
            $queryExperiencesVisibles = clone $queryExperiences;

            $countVisible = $queryExperiencesVisibles->whereVisibleByHoster($hotelModel->id)->count();

            $limit = 20;
            if (!empty($request->limit)) {
                $limit = $request->limit;
            }

            $productspaginate = $queryExperiences->paginate($limit)->appends(request()->except('page'));
            
            // return $products->total();
            $countHidden = $productspaginate->total() - $countVisible;

            $productsCollection = new ExperiencePaginateResource($productspaginate);
            
            $data = [
                'visibleNumbers' => $countVisible,
                'hiddenNumbers' => $countHidden,
                'experiences' => $productsCollection,
            ];
            
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    public function getNumbersByFilters (Request $request) {
        try {

            $hotelModel = $request->attributes->get('hotel');

            $lengthAExpFeatured = 12;
            $hotelId = $hotelModel->id;
            $priceMin = $request->price_min ?? null;
            $priceMax = $request->price_max ?? null;
            $priceMax = $request->price_max ?? null;
            $priceMax = $request->price_max ?? null;
            $search = $request->search ?? null;
            $cityName = $request->city ?? $hotelModel->zone;       
            $featured = $request->featured && $request->featured != 'false' && $request->featured != '0';
            $all_cities = boolval($request->all_cities) ?? false;
            $city_latitude = $request->city_latitude;
            $city_longitude = $request->city_longitude;
            $one_exp_id = $request->one_exp_id ?? null;
            $visibility = $request->visibility ?? null;
            $duration = [];
            if (!empty($request->duration)) {
                $duration = gettype($request->duration) == 'string' ? json_decode($request->duration, true) : $request->duration;
            }

            //crear array de ciudades para la consulta
            $citySlug = Str::slug($hotelModel->zone);
            $cityModel  = $this->cityService->findByParams([ 'slug' => $citySlug]);

            $dataFilter = [
                'city' => $cityName,
                'all_cities' => $all_cities,
                'search' => $search,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'duration' => $duration,
                'score' => $request->score ?? [],
                'free_cancelation' => $request->free_cancelation ?? null,
                'featured' => $featured,
                'one_exp_id' => $one_exp_id,
                'visibility' => $visibility,
            ];

            $numbersByFilters = $this->service->getNumbersByFilters($request, $hotelModel, $dataFilter, $cityModel);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $numbersByFilters);

        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getNumbersByFilters');
        }
    }


    // public function deleteByHoster (Request $request) {
    //     try {
    //         \DB::beginTransaction();
    //         $modelHotel = loadHotel($request);
    //         $modelPlace = Places::find($request->id);
    //         $modelPlace->status = 0;
    //         $modelPlace->save();
    //         \DB::commit();
    //         return bodyResponseRequest(EnumResponse::SUCCESS_OK);
    //     } catch (\Exception $e) {
    //         \DB::rollback();
    //         return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.deleteByHoster');
    //     }
    // }

    public function updatePosition (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $citySlug = Str::slug($hotelModel->zone);
            $cityModel  = $this->cityService->findByParams([ 'slug' => $citySlug]);
            \DB::beginTransaction();
            $this->service->updatePositionBulk($request->position, $hotelModel);
            $this->service->syncPosition($request, $cityModel, $hotelModel);
            \DB::commit();
            return bodyResponseRequest(EnumResponse::SUCCESS_OK);
        } catch (\Exception $e) {
            return $e;
            \DB::rollback();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updatePosition');
        }
    }

    public function resetPosition (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $citySlug = Str::slug($hotelModel->zone);
            $cityModel  = $this->cityService->findByParams([ 'slug' => $citySlug]);
            \DB::beginTransaction();
            $this->service->resetPosition($request, $cityModel, $hotelModel);
            \DB::commit();
            return bodyResponseRequest(EnumResponse::SUCCESS_OK);
        } catch (\Exception $e) {
            return $e;
            \DB::rollback();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updatePosition');
        }
    }

    public function updateVisibility (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $citySlug = Str::slug($hotelModel->zone);
            $cityModel  = $this->cityService->findByParams([ 'slug' => $citySlug]);
            \DB::beginTransaction();
            $productId = $request->product_id;
            $productModel = Products::find($productId);
            $this->service->updateVisibility($request, $hotelModel, $productModel);
            $this->service->syncPosition($request, $cityModel, $hotelModel);
            \DB::commit();
            $productModel->refresh();
            $data = new ExperienceResource($productModel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return $e;
            \DB::rollback();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisibility');
        }
    }

    public function updateRecommendation (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $citySlug = Str::slug($hotelModel->zone);
            $cityModel  = $this->cityService->findByParams([ 'slug' => $citySlug]);
            \DB::beginTransaction();
            $productId = $request->product_id;
            $productModel = Products::find($productId);
            $featuredBool = $request->recommedation ?? false;
            $r = $this->service->featuredByHoster($featuredBool, $hotelModel, $productModel);
            // $updatePositionOld = $featuredBool ? true : false;
            // $c = $this->service->syncPosition($request, $cityModel, $hotelModel, $updatePositionOld);
            $toggleProductModel = ToggleProduct::where(['products_id' => $productModel->id, 'hotel_id' => $hotelModel->id])->first();
            if ($featuredBool) {
                $r = $this->service->assignFirstPosition($hotelModel, $productModel);
            } else {
                // $position = $this->service->getPositionFirtNonRecommendated($hotelModel, $cityModel);
                // $position = $this->service->getPositionOld($toggleProductModel->order, $hotelModel, $cityModel);
                // $position = ExperienceResource::collection($position);
                // $position = new ExperienceResource($position);
                // return $position;
                // $toggleProductModel->refresh();
                // $this->service->updatePosition($hotelModel, $productModel);
            }
            \DB::commit();
            $this->service->syncPosition($request, $cityModel, $hotelModel, false);
            $toggleProductModel->refresh();
            return bodyResponseRequest(EnumResponse::ACCEPTED, $toggleProductModel);
        } catch (\Exception $e) {
            \DB::rollback();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateRecommendation');
        }
    }

    public function featuredByHoster ($featuredBool, $hotelModel, $productModel) {
        $modelPlaceFeatured = PlaceFeatured::where('place_id',$modelPlace->id)
                                        ->where('hotel_id',$modelHotel->id)
                                        ->first();
        if(!$featuredBool && $modelPlaceFeatured){
            $modelPlaceFeatured->delete();
        }
        if($featuredBool && !$modelPlaceFeatured){
            $modelPlaceFeatured = new PlaceFeatured();
            $modelPlaceFeatured->place_id = $modelPlace->id;
            $modelPlaceFeatured->hotel_id = $modelHotel->id;
            $modelPlaceFeatured->save();
        }
    }

    public function update (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');

            $productId = $request->product_id ?? null;
            $messageRecomendation = $request->recommendation ?? null;
            $messageHtml = strip_tags($messageRecomendation);
            $messageRecomendation = $messageRecomendation && $messageHtml ? $messageRecomendation : null;
            $featuredBool = $request->featured ?? false;

            $inputsUpdateProduct = [
                'recommendation' => $messageRecomendation,
            ];

            // \DB::beginTransaction();

            $productModel = Products::find($productId);

            $citySlug = Str::slug($hotelModel->zone);
            $cityModel  = $this->cityService->findByParams([ 'slug' => $citySlug]);

            $toggleProductOld = $productModel->toggleableHotels()->where('hotel_id', $hotelModel->id)->first();

            $r = $this->service->featuredByHoster($featuredBool, $hotelModel, $productModel);

            if ($featuredBool && !$toggleProductOld) {
                $this->service->assignFirstPosition($hotelModel, $productModel);
                $this->service->syncPosition($request, $cityModel, $hotelModel, false);
            }

            $recomendationModel = $this->service->findRecommendation($hotelModel, $productModel);

            $translate =  $messageRecomendation != $recomendationModel?->message;

            $recomendationModel = $this->service->updateRecommendation($messageRecomendation, $recomendationModel, $hotelModel, $productModel);

            // $r = $this->service->featuredByHoster($featuredBool, $hotelModel, $productModel);
            
            if ($translate) {
                $inputsTranslate = ['recommendation' => $inputsUpdateProduct['recommendation']];
                $dirTemplateTranslate = 'translation/webapp/hotel_input/experience';
                TranslateModelJob::dispatch($dirTemplateTranslate, $inputsTranslate, $this->service, $recomendationModel);
            }

            // \DB::commit();

            $dataResponse = new ExperienceResource($productModel);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $dataResponse);

        } catch (\Exception $e) {
            // \DB::rollback();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.update');
        }
    }

}