<?php

namespace App\Http\Requests\Hotel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'type' => 'nullable|string',
            'category' => 'nullable|numeric',
            'email' => 'required|email',
            // 'phone' => 'required|string',
            // 'phone_optional' => 'nullable|string',
            'address' => 'nullable|string',
            'metting_point_latitude' => 'nullable|string',
            'metting_point_longitude' => 'nullable|string',
            'checkin' => 'nullable|string',
            'checkin_until' => 'nullable|string',
            'checkout' => 'nullable|string',
            'checkout_until' => 'nullable|string',
            'description' => 'nullable|string',
            'delete_imgs' => 'nullable|array',
            'urlInstagram' => 'nullable|string',
            'urlPinterest' => 'nullable|string',
            'urlFacebook' => 'nullable|string',
            'urlX' => 'nullable|string',
            'show_profile' => 'required|boolean',
            'with_wifi' => 'required|boolean',
        ];
    }
}
