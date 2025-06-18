<?php

namespace App\Http\Resources;

use App\Models\StayAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuerySettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            "pre_stay_activate" => $this->pre_stay_activate,
            "pre_stay_comment" => $this->pre_stay_comment[localeCurrent()],
            ///////////////////////
            "in_stay_verygood_request_activate" => $this->in_stay_verygood_request_activate,
            "in_stay_verygood_response_title" => str_replace("[nombreHuesped]", $request->guestName, $this->in_stay_verygood_response_title[localeCurrent()]),
            "in_stay_verygood_response_msg" => $this->translateText($this->in_stay_verygood_response_msg[localeCurrent()]),
            "in_stay_verygood_request_otas" => $this->in_stay_verygood_request_otas,
            "in_stay_verygood_no_request_comment_activate" => $this->in_stay_verygood_no_request_comment_activate,
            "in_stay_verygood_no_request_comment_msg" => $this->in_stay_verygood_no_request_comment_msg[localeCurrent()],
            "in_stay_verygood_no_request_thanks_title" => str_replace("[nombreHuesped]", $request->guestName, $this->in_stay_verygood_no_request_thanks_title[localeCurrent()]),
            "in_stay_verygood_no_request_thanks_msg" => $this->in_stay_verygood_no_request_thanks_msg[localeCurrent()],
            //
            "in_stay_good_request_activate" => $this->in_stay_good_request_activate,
            "in_stay_good_response_title" => str_replace("[nombreHuesped]", $request->guestName, $this->in_stay_good_response_title[localeCurrent()]),
            "in_stay_good_response_msg" => $this->translateText($this->in_stay_good_response_msg[localeCurrent()]),
            "in_stay_good_request_otas" => $this->in_stay_good_request_otas,
            "in_stay_good_no_request_comment_activate" => $this->in_stay_good_no_request_comment_activate,
            "in_stay_good_no_request_comment_msg" => $this->in_stay_good_no_request_comment_msg[localeCurrent()],
            "in_stay_good_no_request_thanks_title" => str_replace("[nombreHuesped]", $request->guestName, $this->in_stay_good_no_request_thanks_title[localeCurrent()]),
            "in_stay_good_no_request_thanks_msg" => $this->in_stay_good_no_request_thanks_msg[localeCurrent()],
            //
            "in_stay_bad_response_title" => str_replace("[nombreHuesped]", $request->guestName, $this->in_stay_bad_response_title[localeCurrent()]),
            "in_stay_bad_response_msg" => $this->in_stay_bad_response_msg[localeCurrent()],
            ///////////////////////
            "post_stay_verygood_response_title" => str_replace("[nombreHuesped]", $request->guestName, $this->post_stay_verygood_response_title[localeCurrent()]),
            "post_stay_verygood_response_msg" => $this->translateText($this->post_stay_verygood_response_msg[localeCurrent()]),
            "post_stay_verygood_request_otas" => $this->post_stay_verygood_request_otas,
            //
            "post_stay_good_request_activate" => $this->post_stay_good_request_activate,
            "post_stay_good_response_title" => str_replace("[nombreHuesped]", $request->guestName, $this->post_stay_good_response_title[localeCurrent()]),
            "post_stay_good_response_msg" => $this->translateText($this->post_stay_good_response_msg[localeCurrent()]),
            "post_stay_good_request_otas" => $this->post_stay_good_request_otas,
            "post_stay_good_no_request_comment_activate" => $this->post_stay_good_no_request_comment_activate,
            "post_stay_good_no_request_comment_msg" => $this->post_stay_good_no_request_comment_msg[localeCurrent()],
            "post_stay_good_no_request_thanks_title" => str_replace("[nombreHuesped]", $request->guestName, $this->post_stay_good_no_request_thanks_title[localeCurrent()]),
            "post_stay_good_no_request_thanks_msg" => $this->post_stay_good_no_request_thanks_msg[localeCurrent()],
            //
            "post_stay_bad_response_title" => str_replace("[nombreHuesped]", $request->guestName, $this->post_stay_bad_response_title[localeCurrent()]),
            "post_stay_bad_response_msg" => $this->post_stay_bad_response_msg[localeCurrent()],
            
        ];
    }

    public function translateText($text){
        try {
            $linkText = "[Enlaces a OTAs]";
            $parts = explode("<p><strong>$linkText</strong></p><p><br></p>", $text);

            $text1 = $parts[0] ?? null;
            $text2 = $parts[1] ?? null;

            return [
                "part1" => $text1,
                "part2" => $text2
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }
}
