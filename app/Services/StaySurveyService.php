<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\Facility;
use App\Models\FacilityHoster;
use App\Models\User;
use App\Models\Stay;
use App\Models\StaySurvey;

use App\Http\Resources\FacilityResource;

class StaySurveyService {

    function __construct()
    {

    }

    public function findByParams ($request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $stayId = $request->stay_id ?? null;
            $guestId = $request->guest_id ?? null;
            $survey = StaySurvey::where(['guest_id' => $guestId, 'stay_id' => $stayId])->first();
            return $survey;
        } catch (\Exception $e) {
            $e;
        }

    }

    public function store ($request, $modelHotel) {
        try {
            $stay = Stay::find($request->stay_id);
            $survey = $stay->staySurvey()->where('guest_id', $request->guest_id)->first();
            if (!$survey) {
                $survey = $stay->staySurvey()->create([
                    'guest_id' => $request->guest_id,
                    'score' => $request->type,
                    'description' => $request->description,
                    'steps' => json_encode($request->steps)
                ]);
            } else {
                $survey = $survey->update([
                    'steps' => json_encode($request->steps)
                ]);
            }
            return $survey;
        } catch (\Exception $e) {
            $e;
        }

    }
}