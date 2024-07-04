<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stay;
use App\Utils\Enums\EnumResponse;
use App\Services\QueryServices;

class DasboardController extends Controller
{
    public $queryservice;

    public function __construct(
        QueryServices $_queryservice,

    ) {
        $this->queryservice = $_queryservice;
    }

    public function dataCustomerExperience()
    {
        try {
            $stays = Stay::withCount('guests')
                ->where('hotel_id', request()->hotel['id'])
                ->get();

            $p = $stays->map(function ($stay) {
                $period = $this->queryservice->getCurrentPeriod($stay->hotel_id, $stay->id);
                return [
                    'period' => $period,
                    'guests_count' => $stay->guests_count
                ];
            });

            $count = $p->pluck('period')->countBy()->all(); // Contar cuántas veces se repite cada periodo

            $preStayGuests = $p->where('period', 'pre-stay')->sum('guests_count');
            $inStayGuests = $p->where('period', 'in-stay')->sum('guests_count');
            $postStayGuests = $p->where('period', 'post-stay')->sum('guests_count');

            $languages = $stays->flatMap(function ($stay) {
                return $stay->guests->pluck('lang_web');
            })->countBy(); // Contar la cantidad de cada idioma

            $totalGuests = $languages->sum(); // Total de huéspedes
            $languagePercentages = $languages->map(function ($count, $lang) use ($totalGuests) {
                $percentage = $count / $totalGuests * 100;
                return [
                    'name' => $lang,
                    'percentaje' => round($percentage) ?? 0
                ];
            });

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'postStay' => $count['post-stay'] ?? 0,
                'preStay' => $count['pre-stay'] ?? 0,
                'inStay' => $count['in-stay'] ?? 0,
                'guestsPreStay' => $preStayGuests ?? 0,
                'guestsStay' => $inStayGuests ?? 0,
                'guestsPostStay' => $postStayGuests ?? 0,
                'languages' => $languagePercentages
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage()
            ], null, $e->getMessage());
        }
    }


}
