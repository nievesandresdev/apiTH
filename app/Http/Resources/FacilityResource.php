<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (localeCurrent() == 'es') {
            $title = $this->title;
            $description = $this->description;
            $schedule = $this->schedule;
            $ad_tag = $this->ad_tag;
        } else {
            $title = $this->translate->title ?? null;
            $description = $this->translate->description ?? null;
            $schedule = $this->translate->schedule ?? null;
            $ad_tag = $this->translate->ad_tag ?? null;
        }
        return [
            'id' => $this->id,
            'image' => $this->images->first(),
            'images' => $this->images,
            'title' => $title,
            'description' => $description,
            'schedule' => $schedule,
            'select' => $this->select,
            'visible' => $this->visible,
            'status' => $this->status,
            'schedules' => $this->schedules,
            'always_open' => $this->always_open,
            'ad_tag' => $ad_tag,
            'order' => $this->order,
            'document' => $this->document,
            'document_file' => $this->document_file,
            'text_document_button' => $this->text_document_button,
            'document_file' => $this->document_file,
            'link_document_url' => $this->link_document_url,
        ];
    }
}
