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

    public function getCrosselling ($modelHotel, $limit = 12) {
        try {

            $facilities = FacilityHoster::with('images', 'translate')
                            ->where('hotel_id',$modelHotel->id)
                            ->where('visible',1)
                            ->where('select',1)
                            ->orderBy('order')
                            ->orderBy('updated_at', 'desc')
                            ->limit($limit)
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
        $title = $request->title ?? 'Nueva Instalación';
        $title = $this->generateUniqueTitle($title, $hotelModel->id,$request->id);

        // Handle document if provided
        $documentPath = null;
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $documentPath = saveDocumentOrImage($file, 'facility_documents', $hotelModel->id);
        }

        if($request->id){
            $facilityHosterModel = FacilityHoster::find($request->id);
            $facilityHosterModel->update([
                'title' => $title,
                'description' => $request->description,
                'schedules' => $request->schedules ? $request->schedules : null,
                'ad_tag' => $request->ad_tag ?? null,
                'always_open' => $request->always_open ?? false,
                'document' => $request->document,
                'document_file' => $documentPath ?? $facilityHosterModel->document_file,
                'text_document_button' => $request->text_document_button,
                'link_document_url' => $request->link_document_url,
            ]);
        }else{
            $facilityHosterModel  = FacilityHoster::create([
                'title' => $title,
                'description' => $request->description,
                'status' => 1,
                'select' => 1,
                'user_id' =>  $hotelModel->user[0]->id,
                'hotel_id' => $hotelModel->id,
                'schedules' => $request->schedules ? $request->schedules: null,
                'ad_tag' => $request->ad_tag ?? null,
                'order' => 0,
                'always_open' => $request->always_open ?? false,
                'document' => $request->document,
                'document_file' => $documentPath,
                'text_document_button' => $request->text_document_button,
                'link_document_url' => $request->link_document_url,
            ]);
        }

        $facilityHosterModel = $facilityHosterModel->refresh();
        return $facilityHosterModel;
    }

    public function updateImages ($images, $facilityHosterModel, $hotelModel) {
        // Handle null or string JSON
        if (is_null($images)) {
            $images = [];
        } else if (is_string($images)) {
            $images = json_decode($images, true) ?? [];
        }

        $images = collect($images);

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
                $id = $imgDelete->id;
                $imgDelete->delete();
                return $id;
            });
        }

        if ((count($facilityHosterModel->images) < 1) && !empty($hotelModel->image) && (count($imagesNew) < 1)) {
            $imagesNew[] = ['url' => $hotelModel->image, 'type' => 'STORAGE'];
        }

        foreach ($imagesNew as $item) {
            $facilityHosterModel->images()->create([
                'url' => $item['url'],
                'type' => $item['type']
            ]);
        }
    }

    /* private function generateUniqueTitle($baseTitle, $hotelId)
    {
        $originalTitle = $baseTitle;
        $count = 1;

        // Repite el proceso hasta que encuentres un título que no exista
        while (FacilityHoster::where('hotel_id', $hotelId)->where('title', $baseTitle)->where('visible',1)->exists()) {
            $count++;
            $baseTitle = $originalTitle . ' ' . $count;
        }

        return $baseTitle;
    } */

    private function generateUniqueTitle($baseTitle, $hotelId, $currentId = null)
    {
        $originalTitle = $baseTitle;
        $count = 1;

        while (FacilityHoster::where('hotel_id', $hotelId)
            ->where('title', $baseTitle)
            ->where('visible', 1)
            ->when($currentId, function ($query) use ($currentId) {
                return $query->where('id', '!=', $currentId);
            })
            ->exists()) {
            $count++;
            $baseTitle = $originalTitle . ' ' . $count;
        }

        return $baseTitle;
    }

    public function translateAll () {

        $lgsAll = getAllLanguages()->toArray();

        $dateYearCurrent = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', now())->year;

        $query = FacilityHoster::query()->whereYear('created_at', $dateYearCurrent)->whereHas('translations', function ($query) use ($lgsAll) {
            $query->whereIn('language', $lgsAll);
        }, '<', count($lgsAll));

        // $facilityCollection = $query->limit(1)->get();
        // $facilityCollection = $query->get();

        var_dump("Numbers of faciltiies: ". "{$query->count()}");

        $query->chunk(50, function($facilityCollection) use($lgsAll){
            foreach ($facilityCollection as $facilityHosterModel) {
                var_dump("facility:". $facilityHosterModel->id);
                $translations = collect($facilityHosterModel->translations);

                $lgsWithTranslations = $translations->pluck('language')->toArray();
                $lgsWithoutTranslations = array_values(array_diff($lgsAll, $lgsWithTranslations));
                $dirTemplateTranslate = 'translation/webapp/hotel_input/facility';
                $inputsTranslate = [
                    'title' => $facilityHosterModel->title,
                    'description' => $facilityHosterModel->description,
                    'schedule' => $facilityHosterModel->ad_tag ?? ''
                ];
                if((
                    !empty($inputsTranslate['title']) ||
                    !empty($inputsTranslate['description'])) &&
                    !empty($lgsWithoutTranslations)
                ) {
                    TranslateModelJob::dispatchSync($dirTemplateTranslate, $inputsTranslate, $this, $facilityHosterModel, $lgsWithoutTranslations);
                }
            }
        });

    }

    public function processTranslate ($request, $facilityHosterModel, $hotelModel) {

        $dirTemplateTranslate = 'translation/webapp/hotel_input/facility';
        $inputsTranslate = [
            'title' => $facilityHosterModel->title,
            'description' => $facilityHosterModel->description,
            'schedule' => $request->ad_tag
        ];
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

    public function delete ($id, $hotelModel) {
        $facilityHosterModel = FacilityHoster::find($id);
        $facilityHosterModel->update(['visible' => 0]);
    }

}
