<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Stay;

class PeriodicityFeedback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:periodicity-feedback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notificaciones de feedback pendientes según la periodicidad configurada.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        try {
            // Consultar las estancias con feedback pendiente
            $stays = Stay::with(['hotel.user', 'queries'])
                ->whereHas('queries', function ($q) {
                    $q->where('answered', 1)
                        ->where('attended', 0);
                })
                ->get();

            // Procesar cada estancia
            $stays->each(function ($stay) use ($now) {
                $stay->hotel->user->each(function ($user) use ($stay, $now) {
                    $lastFeedbackNotified = $user->feedback_last_notified_at ? Carbon::parse($user->feedback_last_notified_at) : null;

                    if (!$lastFeedbackNotified || $lastFeedbackNotified->diffInMinutes($now) >= $user->periodicity_stay) {
                        // Enviar notificación de feedback pendiente
                        sendEventPusher(
                            'notify-send-query.' . $stay->hotel_id,
                            'App\Events\NotifySendQueryEvent',
                            [
                                "userId" => $user->id,
                                "stayId" => $stay->id,
                                "title" => "Feedback Pendiente",
                                "text" => "Tienes un Feedback pendiente",
                                "concept" => "pending",
                            ]
                        );

                        // Actualizar la última notificación
                        $user->update(['feedback_last_notified_at' => $now]);
                    }
                });
            });

            Log::info('Notificaciones de feedback pendientes enviadas con éxito.');
        } catch (\Exception $e) {
            Log::error('Error en PeriodicityFeedback: ' . $e->getMessage());
        }
    }
}
