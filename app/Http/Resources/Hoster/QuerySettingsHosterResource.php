<?php

namespace App\Http\Resources\Hoster;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuerySettingsHosterResource extends JsonResource
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
            "pre_stay_activate" => $this->pre_stay_activate,
            "pre_stay_thanks" => $this->pre_stay_thanks,
            "pre_stay_comment" => $this->pre_stay_comment,
            "in_stay_activate" => $this->in_stay_activate,
            "in_stay_thanks_good" => $this->in_stay_thanks_good,
            "in_stay_assessment_good_activate" => $this->in_stay_assessment_good_activate,
            "in_stay_assessment_good" => $this->in_stay_assessment_good,
            "in_stay_thanks_normal" => $this->in_stay_thanks_normal,
            "in_stay_assessment_normal_activate" => $this->in_stay_assessment_normal_activate,
            "in_stay_assessment_normal" => $this->in_stay_assessment_normal,
            "in_stay_comment" => $this->in_stay_comment,
            "post_stay_thanks_good" => $this->post_stay_thanks_good,
            "post_stay_assessment_good_activate" => $this->post_stay_assessment_good_activate,
            "post_stay_assessment_good" => $this->post_stay_assessment_good,
            "post_stay_thanks_normal" => $this->post_stay_thanks_normal,
            "post_stay_assessment_normal_activate" => $this->post_stay_assessment_normal_activate,
            "post_stay_assessment_normal" => $this->post_stay_assessment_normal,
            "post_stay_comment" => $this->post_stay_comment,
            "notify_to_hoster" => $this->notify_to_hoster,
            "email_notify_new_feedback_to" => $this->email_notify_new_feedback_to,
            "email_notify_pending_feedback_to" => $this->email_notify_pending_feedback_to
        ];

        if (empty($this->fields)) {
            return $allData;
        }

        return array_intersect_key($allData, array_flip($this->fields));
    }
}
