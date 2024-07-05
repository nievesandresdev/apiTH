<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stay;
use App\Utils\Enums\EnumResponse;
use App\Services\QueryServices;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class DasboardController extends Controller
{
    public $queryservice;

    public function __construct(
        QueryServices $_queryservice,

    ) {
        $this->queryservice = $_queryservice;
    }

    public function dataCustomerExperience(Request $request)
    {
        try {
            $hotel = $request->attributes->get('hotel');
            Log::info('Hotel ID:', ['hotel_id' => $hotel->id]);

            $stays = Stay::withCount('guests')
                ->where('hotel_id', $hotel->id)
                ->get();

            Log::info('Stays:', ['stays' => $stays]);

            $p = $stays->map(function ($stay) {
                $period = $this->queryservice->getCurrentPeriod($stay->hotel_id, $stay->id);

                Log::info('Period and Guests Count:', ['period' => $period, 'guests_count' => $stay->guests_count]);

                return [
                    'period' => $period,
                    'guests_count' => $stay->guests_count
                ];
            });

            Log::info('Mapped Collection:', ['collection' => $p]);

            $count = $p->pluck('period')->countBy()->all(); // Contar cuántas veces se repite cada periodo

            Log::info('Period Count:', ['count' => $count]);

            $preStayGuests = $p->where('period', 'pre-stay')->sum('guests_count');
            $inStayGuests = $p->where('period', 'in-stay')->sum('guests_count');
            $postStayGuests = $p->where('period', 'post-stay')->sum('guests_count');

            Log::info('Guests Count by Period:', [
                'preStayGuests' => $preStayGuests,
                'inStayGuests' => $inStayGuests,
                'postStayGuests' => $postStayGuests
            ]);

            $languages = $stays->flatMap(function ($stay) {
                return $stay->guests->pluck('lang_web');
            })->countBy(); // Contar la cantidad de cada idioma

            Log::info('Languages Count:', ['languages' => $languages]);

            $totalGuests = $languages->sum(); // Total de huéspedes
            $languagePercentages = $languages->map(function ($count, $lang) use ($totalGuests) {
                $percentage = $count / $totalGuests * 100;
                return [
                    'name' => $lang,
                    'percentaje' => round($percentage) ?? 0
                ];
            });

            Log::info('Language Percentages:', ['languagePercentages' => $languagePercentages]);

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
            Log::error('Error in dataCustomerExperience:', ['message' => $e->getMessage()]);

            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage()
            ], null, $e->getMessage());
        }
    }

    public function dataFeedback(Request $request)
    {
        try {
            $hotel = $request->attributes->get('hotel');
            Log::info('Hotel ID:', ['hotel_id' => $hotel->id]);

            // Rconsulta
            $stays = Stay::with(['queries' => function($query) {
                    $query->where('answered', 1)->whereNotNull('qualification');
                }, 'guests'])
                ->where('hotel_id', $hotel->id)
                ->withCount('guests')
                ->get();

            Log::info('Stays:', ['stays' => $stays]);

            // consulta x periodo
            $inStayQueries = $stays->flatMap->queries->where('period', 'in-stay');
            $postStayQueries = $stays->flatMap->queries->where('period', 'post-stay');

            $possibleQualifications = ['GOOD', 'VERYGOOD', 'NORMAL', 'WRONG', 'VERYWRONG'];

            // porcentajes para in-stay
            $inStayQualificationCount = $inStayQueries->pluck('qualification')->countBy();
            $totalInStay = $inStayQualificationCount->sum();

            $inStayPercentages = collect($possibleQualifications)->mapWithKeys(function ($qualification) use ($inStayQualificationCount, $totalInStay) {
                $count = $inStayQualificationCount->get($qualification, 0);
                $percentage = $totalInStay > 0 ? round(($count / $totalInStay) * 100) : 0;
                return [$qualification => $percentage];
            });

            // porcentajes para post-stay
            $postStayQualificationCount = $postStayQueries->pluck('qualification')->countBy();
            $totalPostStay = $postStayQualificationCount->sum();

            $postStayPercentages = collect($possibleQualifications)->mapWithKeys(function ($qualification) use ($postStayQualificationCount, $totalPostStay) {
                $count = $postStayQualificationCount->get($qualification, 0);
                $percentage = $totalPostStay > 0 ? round(($count / $totalPostStay) * 100) : 0;
                return [$qualification => $percentage];
            });

            Log::info('In-Stay Percentages:', ['inStay' => $inStayPercentages]);
            Log::info('Post-Stay Percentages:', ['postStay' => $postStayPercentages]);

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'inStay' => $inStayPercentages,
                'postStay' => $postStayPercentages
            ]);
        } catch (\Exception $e) {
            Log::error('Error in dataFeedback:', ['message' => $e->getMessage()]);

            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage()
            ], null, $e->getMessage());
        }
    }







}
