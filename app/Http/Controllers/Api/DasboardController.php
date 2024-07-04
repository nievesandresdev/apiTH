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
            // Registro para verificar el hotel ID de la solicitud
            \Log::info('Hotel ID:', ['hotel_id' => request()->hotel['id']]);

            $stays = Stay::withCount('guests')
                ->where('hotel_id', request()->hotel['id'])
                ->get();

            // Registro para verificar las estadías obtenidas
            \Log::info('Stays:', ['stays' => $stays]);

            $p = $stays->map(function ($stay) {
                $period = $this->queryservice->getCurrentPeriod($stay->hotel_id, $stay->id);

                // Registro para verificar el periodo y el número de huéspedes
                \Log::info('Period and Guests Count:', ['period' => $period, 'guests_count' => $stay->guests_count]);

                return [
                    'period' => $period,
                    'guests_count' => $stay->guests_count
                ];
            });

            // Registro para verificar la colección mapeada
            \Log::info('Mapped Collection:', ['collection' => $p]);

            $count = $p->pluck('period')->countBy()->all(); // Contar cuántas veces se repite cada periodo

            // Registro para verificar el conteo de periodos
            \Log::info('Period Count:', ['count' => $count]);

            $preStayGuests = $p->where('period', 'pre-stay')->sum('guests_count');
            $inStayGuests = $p->where('period', 'in-stay')->sum('guests_count');
            $postStayGuests = $p->where('period', 'post-stay')->sum('guests_count');

            // Registro para verificar el número de huéspedes en cada periodo
            \Log::info('Guests Count by Period:', [
                'preStayGuests' => $preStayGuests,
                'inStayGuests' => $inStayGuests,
                'postStayGuests' => $postStayGuests
            ]);

            $languages = $stays->flatMap(function ($stay) {
                return $stay->guests->pluck('lang_web');
            })->countBy(); // Contar la cantidad de cada idioma

            // Registro para verificar los idiomas contados
            \Log::info('Languages Count:', ['languages' => $languages]);

            $totalGuests = $languages->sum(); // Total de huéspedes
            $languagePercentages = $languages->map(function ($count, $lang) use ($totalGuests) {
                $percentage = $count / $totalGuests * 100;
                return [
                    'name' => $lang,
                    'percentaje' => round($percentage) ?? 0
                ];
            });

            // Registro para verificar los porcentajes de idiomas
            \Log::info('Language Percentages:', ['languagePercentages' => $languagePercentages]);

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
            // Registro para capturar el mensaje de error
            \Log::error('Error in dataCustomerExperience:', ['message' => $e->getMessage()]);

            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage()
            ], null, $e->getMessage());
        }
    }



}
