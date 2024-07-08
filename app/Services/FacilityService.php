<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\Facility;
use App\Models\FacilityHoster;
use App\Models\FacilityHosterLanguage;
use App\Models\User;

use App\Http\Resources\FacilityResource;

use App\Jobs\TranslateModelJob;

class FacilityService {

    function __construct()
    {

    }

    public function getCrosselling ($modelHotel) {
        try {

            $facilities = FacilityHoster::with('images', 'translate')
                            ->where('hotel_id',$modelHotel->id)
                            ->where('visible',1)
                            ->where('select',1)
                            ->orderBy('order')
                            ->orderBy('updated_at', 'desc')
                            ->limit(12)
                            ->get();

            return $facilities;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getAll ($request, $modelHotel) {
        try {
            $query = FacilityHoster::with(['images', 'translations'])
                ->where('hotel_id',$modelHotel->id)
                ->where(['status' => 1])->where('visible',1);
            if (isset($request->visible)) {
                $query = $query->where(['select' => $request->visible]);
            }
            $facilities = $query->orderBy('order')->get();
                
            return $facilities;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findById ($id, $modelHotel) {
        try {
            $facility = FacilityHoster::with('images')
                ->where('id',$id)
                ->where('hotel_id',$modelHotel->id)
                // ->where(['status' => 1, 'select' => 1])->where('visible',1)
                ->first();
                
            return $facility;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updateOrder ($order, $hotelModel) {
        $facilitiesIdsOrded = collect($order??[]);
        foreach ($facilitiesIdsOrded as $position => $id) {
            FacilityHoster::where(['id' => $id])->update(['order' => $position]);
        }
    }
    public function syncOrder ($hotelModel) {
        $facilitiesIdsOrded = $hotelModel->facilities()->whereVisible()->orderBy('order')->orderBy('updated_at', 'desc')->pluck('id');
        foreach ($facilitiesIdsOrded as $position => $id) {
            FacilityHoster::where(['id' => $id])->update(['order' => $position]);
        }
    }

    public function updateVisible ($request, $facilityHosterModel) {
        $facilityHosterModel->update(['select' => $request->visible, 'order' => 0]);
    }

    public function storeOrUpdate ($request, $hotelModel) {
        if($request->id){
            $facilityHosterModel = FacilityHoster::find($request->id);
            $facilityHosterModel->update([
                'title' => $request->title,
                'description' => $request->description,
                // 'schedule' =>  $request->schedule,
                'schedules' => $request->schedules ? json_encode($request->schedules) : null,
                'ad_tag' => $request->ad_tag ?? null,
            ]);
        }else{
            $order = $hotelModel->facilities()->whereVisible()->count() - 1;
            $facilityHosterModel  = FacilityHoster::create([
                'title' => $request->title,
                'description' => $request->description,
                // 'schedule' =>  $request->schedule,
                'status' => 1,
                'select' => 1,
                'user_id' =>  $hotelModel->user[0]->id,
                'hotel_id' => $hotelModel->id,
                'schedules' => $request->schedules ? json_encode($request->schedules) : null,
                'ad_tag' => $request->ad_tag ?? null,
                'order' => $order
            ]);
        }
        $facilityHosterModel = $facilityHosterModel->refresh();
        return $facilityHosterModel;
    }

    public function updateImages ($images, $facilityHosterModel) {
        $images = collect($images ?? []);
        $imagesNew = $images->filter(function ($item) {
            return !isset($item['id']) || empty($item['id']);
        });
        $imagesOld = $images->filter(function ($item) {
            return isset($item['id']);
        });
        $imagesCurrent = $facilityHosterModel->images ?? [];

        $deletedIds = [];

        if (count($imagesCurrent) > 0) {
            $deletedIds = $imagesCurrent->filter(function ($childImg) use ($imagesOld) {
                return empty($imagesOld->where('id', $childImg->id)->first());
            })->map(function ($imgDelete) {
                $id = $img_delete->id;
                $imgDelete->delete();
                return $id;
            });
        }

        foreach ($imagesNew as $item) {
            $facilityHosterModel->images()->create([
                'url' => $item['url'],
                'type' => $item['type']
            ]);
        }
    }

    public function processTranslate ($request, $facilityHosterModel, $hotelModel) {

        $dirTemplateTranslate = 'translation/webapp/hotel_input/facility';
        $inputsTranslate = ['title' => $request->title, 'description' => $request->description, 'schedule' => $request->ad_tag];
        TranslateModelJob::dispatch($dirTemplateTranslate, $inputsTranslate, $this, $facilityHosterModel);
    }

    public function updateTranslation ($model, $translation) {
        try{
            if (!$translation) return;
            
            foreach ($translation as $lg => $value) {
                $title = !isset($value->title) || !$value->title || ($value->title == 'null') ? null : $value->title;
                $description = !isset($value->description) || !$value->description || ($value->description == 'null') ? null : $value->description;
                $schedule = !isset($value->schedule) || !$value->schedule || ($value->schedule == 'null') ? null : $value->schedule;
                FacilityHosterLanguage::updateOrCreate([
                    'language' => $lg,
                    'facility_hoster_id' => $model['id'],
                ],[
                    'title' => $title,
                    'description' => $description,
                    'language' => $lg,
                    'ad_tag' => $schedule,
                    'facility_hoster_id' => $model['id'],
                ]);
            }

        } catch (\Exception $e) {
            $message = $e->getMessage();
            Log::error('updateTranslation:'.' '. $message);
            return $e;
        }
    }
    
}