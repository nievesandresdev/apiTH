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


   
}
