<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            "id"=> $this->id,
            "name"=> $this->name,
            "lastname" => $this->lastname,
            "avatar"=> $this->avatar,
            "avatar_type" => $this->avatar_type,
            "lang_web"=> $this->lang_web,
            "email"=> $this->email,
            "phone"=> $this->phone,
            "hasPassword"=>  boolval($this->password),
            //
            "birthdate"=> $this->birthdate,
            "sex"=> $this->sex,
            "doc_type"=> $this->doc_type,
            "doc_num"=> $this->doc_num,
            "nationality"=> $this->nationality,
            "address"=> $this->address,
            "second_lastname"=> $this->second_lastname,
            "responsible_adult"=> $this->responsible_adult,
            "kinship_relationship"=> $this->kinship_relationship,
            "doc_support_number"=> $this->doc_support_number,
            "postal_code"=> $this->postal_code,
            "municipality"=> $this->municipality,
            "country_address"=> $this->country_address,
            "checkin_email"=> $this->checkin_email,
            //
            "complete_checkin_data"=> $this->complete_checkin_data,
            
        ];
    }
}
