<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatSettingResource extends JsonResource
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
            "name" => $this->name,
            "languages" => $this->languages,
            "show_guest" => $this->show_guest,
            "hotel_id" => $this->hotel_id ?? null,
            "not_available_show" => $this->not_available_show,
            "not_available_msg" => $this->not_available_msg,
            "first_available_msg" => $this->first_available_msg,
            "first_available_show" => $this->first_available_show,
            "second_available_msg" => $this->second_available_msg,
            "second_available_show" => $this->second_available_show,
            "three_available_msg" => $this->three_available_msg,
            "three_available_show" => $this->three_available_show,
            "email_notify_new_message_to" => $this->email_notify_new_message_to,
            "email_notify_pending_chat_to" => $this->email_notify_pending_chat_to,
            "email_notify_not_answered_chat_to" => $this->email_notify_not_answered_chat_to,
        ];

        if (empty($this->fields)) {
            return $allData;
        }

        return array_intersect_key($allData, array_flip($this->fields));
    }
}
