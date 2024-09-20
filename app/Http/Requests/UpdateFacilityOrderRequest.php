<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Hotel;
use App\Models\FacilityHoster;

class UpdateFacilityOrderRequest extends FormRequest
{
    // public function authorize()
    // {
    //     return true;
    // }

    public function rules()
    {
        return [
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:facility_hosters,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $hotel = $this->attributes->get('hotel');

            $facilityIds = collect($this->input('order'));
            $facilities = FacilityHoster::whereIn('id', $facilityIds)
                                  ->where(['hotel_id'=> $hotel->id, 'status' => 1, 'visible' => 1, 'select' => 1])
                                  ->get();

            if ($facilities->count() !== $facilityIds->count()) {
                $validator->errors()->add('order', 'Algunas instalaciones no estan disponibles');
            }
        });
    }
}
