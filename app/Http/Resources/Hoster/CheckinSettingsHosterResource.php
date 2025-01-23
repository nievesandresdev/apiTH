<?php

namespace App\Http\Resources\Hoster;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckinSettingsHosterResource extends JsonResource
{
    protected $fields;

    public function __construct($resource, $fields = [])
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->fields = $fields;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $allData = [
            "hotel_id" => $this->hotel_id ?? null,
            "succes_message" => $this->succes_message,
            "first_step" => $this->first_step,
            "second_step" => $this->second_step,
            "show_prestay_query" => $this->show_prestay_query,
        ];

        if (empty($this->fields)) {
            return $allData;
        }

        return array_intersect_key($allData, array_flip($this->fields));
    }
}
