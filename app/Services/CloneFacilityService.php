<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\FacilityHoster;
use App\Models\ImageFacilty;
use App\Models\FacilityHosterLanguage;

class CloneFacilityService
{

    public function handle($HOTEL_ID_PARENT, $HOTEL_ID_CHILD) {
        $this->cloneFacility($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);
        $this->cloneFacilityImages($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);
        $this->cloneFacilityHosterLanguage($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);
    }

    public function cloneFacility($HOTEL_ID_PARENT, $HOTEL_ID_CHILD) {

        $facilityHostersParent = FacilityHoster::where('hotel_id', $HOTEL_ID_PARENT)->get();
        foreach ($facilityHostersParent as $facilityHosterParent) {

            try {

                DB::beginTransaction();

                if ($facilityHosterParent->son_id == null) {
                    $facilityhosterchild = $facilityHosterParent->replicate();
                $facilityhosterchild->hotel_id = $HOTEL_ID_CHILD;
                $facilityhosterchild->save();

                $facilityHosterParent->son_id = $facilityhosterchild->id;
                $facilityHosterParent->save();
            } else {
                $facilityHosterChild = FacilityHoster::find($facilityHosterParent->son_id);
                if ($facilityHosterChild) {

                    $facilityHosterChild->title = $facilityHosterParent->title;
                    $facilityHosterChild->description = $facilityHosterParent->description;
                    $facilityHosterChild->status = $facilityHosterParent->status;
                    $facilityHosterChild->select = $facilityHosterParent->select;
                    $facilityHosterChild->user_id = $facilityHosterParent->user_id;
                    $facilityHosterChild->hotel_id = $HOTEL_ID_CHILD;
                    $facilityHosterChild->schedules = $facilityHosterParent->schedules;
                    $facilityHosterChild->ad_tag = $facilityHosterParent->ad_tag;
                    $facilityHosterChild->order = $facilityHosterParent->order;
                    $facilityHosterChild->always_open = $facilityHosterParent->always_open;
                    $facilityHosterChild->visible = $facilityHosterParent->visible;
                    $facilityHosterChild->save();

                } else {
                    $facilityhosterchild = $facilityHosterParent->replicate();
                    $facilityhosterchild->hotel_id = $HOTEL_ID_CHILD;
                    $facilityhosterchild->save();

                    $facilityHosterParent->son_id = $facilityhosterchild->id;
                    $facilityHosterParent->save();
                }
            }

            $facilityHostersParentIds = FacilityHoster::where('hotel_id', $HOTEL_ID_PARENT)
                ->whereNotNull('son_id')
                ->pluck('son_id'); //Array de todos los ids de los hijos del hotel padre
            $facilityHosterChild = FacilityHoster::where('hotel_id', $HOTEL_ID_CHILD)
                ->whereNotIn('id', $facilityHostersParentIds)
                ->delete(); //Elimina todos los hijos del hotel hijo que no están en el array de los hijos del hotel padre
            
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
            }

        }
    }

    public function cloneFacilityImages($HOTEL_ID_PARENT, $HOTEL_ID_CHILD) {
        $imageFacilitiesParent = ImageFacilty::whereHas('facilityHoster', function ($query) use ($HOTEL_ID_PARENT) {
            $query->where('hotel_id', $HOTEL_ID_PARENT);
        })->get();
        foreach ($imageFacilitiesParent as $imageFacilityParent) {

            try {       

                DB::beginTransaction();

                $facilityHosterParent = FacilityHoster::find($imageFacilityParent->facility_hoster_id);
                if (!$facilityHosterParent->son_id) {
                    continue;
                }

                $facilityHosterChild = FacilityHoster::find($facilityHosterParent->son_id);

                if (!$facilityHosterChild) {
                    continue;
                }
                if ($imageFacilityParent->son_id == null) {
                    $imageFacilityChild = $imageFacilityParent->replicate();
                    $imageFacilityChild->facility_hoster_id = $facilityHosterChild->id;
                    $imageFacilityChild->save();

                    $imageFacilityParent->son_id = $imageFacilityChild->id;
                    $imageFacilityParent->save();
                } else {
                    $imageFacilityChild = ImageFacilty::find($imageFacilityParent->son_id);
                    if ($imageFacilityChild) {
                        $imageFacilityChild->url = $imageFacilityParent->url;
                        $imageFacilityChild->type = $imageFacilityParent->type;
                        $imageFacilityChild->save();
                    } else {
                        $imageFacilityChild = $imageFacilityParent->replicate();
                        $imageFacilityChild->facility_hoster_id = $facilityHosterChild->id;
                        $imageFacilityChild->save();

                        $facilityHosterChild->son_id = $imageFacilityChild->id;
                        $facilityHosterChild->save();
                    }
                }

                $imageFacilityHostersParentIds = ImageFacilty::whereHas('facilityHoster', function ($query) use ($HOTEL_ID_PARENT) {
                    $query->where('hotel_id', $HOTEL_ID_PARENT);
                })->whereNotNull('son_id')
                ->pluck('son_id'); //Array de todos los ids de los hijos del hotel padre

                $imageFacilityChild = ImageFacilty::whereHas('facilityHoster', function ($query) use ($HOTEL_ID_CHILD) {
                    $query->where('hotel_id', $HOTEL_ID_CHILD);
                })->whereNotIn('id', $imageFacilityHostersParentIds)
                ->delete(); //Elimina todos los hijos del hotel hijo que no están en el array de los hijos del hotel padre
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
            }

        }
    }

    public function cloneFacilityHosterLanguage($HOTEL_ID_PARENT, $HOTEL_ID_CHILD) {
        $facilityHosterLanguagesParent = FacilityHosterLanguage::whereHas('facilityHoster', function ($query) use ($HOTEL_ID_PARENT) {
            $query->where('hotel_id', $HOTEL_ID_PARENT);
        })->get();
        foreach ($facilityHosterLanguagesParent as $facilityHosterLanguageParent) {

            try {       

                DB::beginTransaction();

                $facilityHosterParent = FacilityHoster::find($facilityHosterLanguageParent->facility_hoster_id);
                if (!$facilityHosterParent->son_id) {
                    continue;
                }

                $facilityHosterChild = FacilityHoster::find($facilityHosterParent->son_id);

                if (!$facilityHosterChild) {
                    continue;
                }
                if ($facilityHosterLanguageParent->son_id == null) {
                    $facilityHosterLanguageChild = $facilityHosterLanguageParent->replicate();
                    $facilityHosterLanguageChild->facility_hoster_id = $facilityHosterChild->id;
                    $facilityHosterLanguageChild->save();

                    $facilityHosterLanguageParent->son_id = $facilityHosterLanguageChild->id;
                    $facilityHosterLanguageParent->save();
                } else {
                    $facilityHosterLanguageChild = FacilityHosterLanguage::find($facilityHosterLanguageParent->son_id);
                    if ($facilityHosterLanguageChild) {
                        $facilityHosterLanguageChild->title = $facilityHosterLanguageParent->title;
                        $facilityHosterLanguageChild->description = $facilityHosterLanguageParent->description;
                        $facilityHosterLanguageChild->language = $facilityHosterLanguageParent->language;
                        $facilityHosterLanguageChild->schedule = $facilityHosterLanguageParent->schedule;
                        $facilityHosterLanguageChild->ad_tag = $facilityHosterLanguageParent->ad_tag;   
                        $facilityHosterLanguageChild->save();
                    } else {
                        $facilityHosterLanguageChild = $facilityHosterLanguageParent->replicate();
                        $facilityHosterLanguageChild->facility_hoster_id = $facilityHosterChild->id;
                        $facilityHosterLanguageChild->save();

                        $facilityHosterLanguageParent->son_id = $facilityHosterLanguageChild->id;
                        $facilityHosterLanguageParent->save();
                    }
                }

                $facilityHosterLanguageHostersParentIds = FacilityHosterLanguage::whereHas('facilityHoster', function ($query) use ($HOTEL_ID_PARENT) {
                    $query->where('hotel_id', $HOTEL_ID_PARENT);
                })->whereNotNull('son_id')
                ->pluck('son_id'); //Array de todos los ids de los hijos del hotel padre

                $facilityHosterLanguageChild = FacilityHosterLanguage::whereHas('facilityHoster', function ($query) use ($HOTEL_ID_CHILD) {
                    $query->where('hotel_id', $HOTEL_ID_CHILD);
                })->whereNotIn('id', $facilityHosterLanguageHostersParentIds)
                ->delete(); //Elimina todos los hijos del hotel hijo que no están en el array de los hijos del hotel padre
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
            }

        }
    }
}
