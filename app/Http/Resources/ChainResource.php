<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChainResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $independentSubdomain = $this->hotels()->where('del',0)->first();
        return [
            "id" => $this->id,
            "subdomain" => $this->subdomain,
            "type" => $this->type,
            "independentSubdomain" => $this->type == "INDEPENDENT" ? $independentSubdomain->subdomain : null,
            "length_subdomain_default" => $this->subdomainActive->length_subdomain_default
        ];
    }
}
