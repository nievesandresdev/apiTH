<?php

namespace App\Services\Hoster;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UtilsHosterServices
{
    function calculateDaysOrWeeksFromDate($date) {
        
        $checkout = Carbon::parse($date);
        $now = Carbon::now();
        
        $daysDifference = $checkout->diffInDays($now);

        if ($daysDifference < 7) {
            $textDay = $daysDifference == 1 ? ' dia' : ' dias';
            return "Hace <b>$daysDifference</b> $textDay";
        } else {
            $weeksDifference = floor($daysDifference / 7);
            $textWeek = $weeksDifference == 1 ? ' semana' : ' semanas';
            return "Hace <b>$weeksDifference</b> $textWeek";
        }
    }

    function calculateDaysBetween($checkinDate, $checkoutDate) {
        // Convert the check-in and check-out dates to Carbon instances
        $checkin = Carbon::parse($checkinDate);
        $checkout = Carbon::parse($checkoutDate);
        
        // Calculate the difference in days between check-in and check-out
        $daysDifference = $checkin->diffInDays($checkout);
        
        return $daysDifference;
    }

    function calculateDaysUntilTo($checkinDate) {
        // Convert the check-in date to a Carbon instance and normalize to the start of the day
        $checkin = Carbon::parse($checkinDate)->startOfDay();
        
        // Get the current date and normalize to the start of the day
        $now = Carbon::now()->startOfDay();
        
        // Calculate the difference in days from now until the check-in date
        $daysUntilCheckin = $now->diffInDays($checkin, false); // False to allow negative if the date is in the past
        
        return $daysUntilCheckin;
    }

    function formatDateToDayMonthAndYear($date) {
        
        $carbonDate = Carbon::parse($date);
        $dayMonth = $carbonDate->format('d M');
        $year = $carbonDate->format('Y'); 
    
        return [
            'dayMonth' => $dayMonth,
            'year' => $year
        ];
    }

    function getAllNotesByStay($stayId){
        return DB::select("
            (SELECT ns.id, ns.content, ns.created_at, ns.updated_at, ns.edited, NULL as guest_id, NULL as guest_name, NULL as guest_acronym, NULL as guest_color, 'ES' as type
            FROM note_stays ns
            WHERE ns.stay_id = :stayId1)

            UNION ALL

            (SELECT ng.id, ng.content, ng.created_at, ng.updated_at, ng.edited, ng.guest_id, g.name as guest_name, g.acronym as guest_acronym, g.color as guest_color, 'HU' as type
            FROM note_guests ng
            LEFT JOIN guests g ON ng.guest_id = g.id
            WHERE ng.stay_id = :stayId2)

            ORDER BY created_at DESC
        ", ['stayId1' => $stayId, 'stayId2' => $stayId]);
    }
   
}
