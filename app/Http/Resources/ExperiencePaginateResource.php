<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExperiencePaginateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $paginate = [
            "total" => $this->total(),
            "current_page" => $this->currentPage(),
            "per_page" => $this->perPage(),
            "last_page" => $this->lastPage(),
            "from_page" => $this->firstItem(),
            "to" => $this->lastPage()
        ];
        return [
            "paginate" => $paginate,
             "data" => ExperienceResource::collection($this->items()),
        ];
    }
}
