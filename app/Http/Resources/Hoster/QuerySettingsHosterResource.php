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
            //////////////////////
            "pre_stay_activate" => $this->pre_stay_activate,
            "pre_stay_comment" => $this->pre_stay_comment,
            //////////////////////
            'in_stay_verygood_request_activate' => $this->in_stay_verygood_request_activate,
            'in_stay_verygood_response_title' => $this->in_stay_verygood_response_title,
            'in_stay_verygood_response_msg' => $this->in_stay_verygood_response_msg,
            'in_stay_verygood_request_otas' => $this->in_stay_verygood_request_otas,
            'in_stay_verygood_no_request_comment_activate' => $this->in_stay_verygood_no_request_comment_activate,
            'in_stay_verygood_no_request_comment_msg' => $this->in_stay_verygood_no_request_comment_msg,
            'in_stay_verygood_no_request_thanks_title' => $this->in_stay_verygood_no_request_thanks_title,
            'in_stay_verygood_no_request_thanks_msg' => $this->in_stay_verygood_no_request_thanks_msg,
            //
            'in_stay_good_request_activate' => $this->in_stay_good_request_activate,
            'in_stay_good_response_title' => $this->in_stay_good_response_title,
            'in_stay_good_response_msg' => $this->in_stay_good_response_msg,
            'in_stay_good_request_otas' => $this->in_stay_good_request_otas,
            'in_stay_good_no_request_comment_activate' => $this->in_stay_good_no_request_comment_activate,
            'in_stay_good_no_request_comment_msg' => $this->in_stay_good_no_request_comment_msg,
            'in_stay_good_no_request_thanks_title' => $this->in_stay_good_no_request_thanks_title,
            'in_stay_good_no_request_thanks_msg' => $this->in_stay_good_no_request_thanks_msg,
            //
            'in_stay_bad_response_title' => $this->in_stay_bad_response_title,
            'in_stay_bad_response_msg' => $this->in_stay_bad_response_msg,
            //////////////////////
            'post_stay_verygood_response_title' => $this->post_stay_verygood_response_title,
            'post_stay_verygood_response_msg' => $this->post_stay_verygood_response_msg,
            'post_stay_verygood_request_otas' => $this->post_stay_verygood_request_otas,
            //
            'post_stay_good_request_activate' => $this->post_stay_good_request_activate,
            'post_stay_good_response_title' => $this->post_stay_good_response_title,
            'post_stay_good_response_msg' => $this->post_stay_good_response_msg,
            'post_stay_good_request_otas' => $this->post_stay_good_request_otas,
            'post_stay_good_no_request_comment_activate' => $this->post_stay_good_no_request_comment_activate,
            'post_stay_good_no_request_comment_msg' => $this->post_stay_good_no_request_comment_msg,
            'post_stay_good_no_request_thanks_title' => $this->post_stay_good_no_request_thanks_title,
            'post_stay_good_no_request_thanks_msg' => $this->post_stay_good_no_request_thanks_msg,
            //
            'post_stay_bad_response_title' => $this->post_stay_bad_response_title,
            'post_stay_bad_response_msg' => $this->post_stay_bad_response_msg,
        ];

        if (empty($this->fields)) {
            return $allData;
        }

        return array_intersect_key($allData, array_flip($this->fields));
    }
}
