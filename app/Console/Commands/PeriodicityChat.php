<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Stay;

class PeriodicityChat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:periodicity-chat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filters = ['periods' => ['in-stay', 'pre-stay']];

        try {
            $now = Carbon::now();
            $limit = $filters['limit'] ?? 10;
            $offset = $filters['offset'] ?? 0;

            $query = Stay::with([
                'chats' => function ($q) {
                    $q->where('pending', 1)
                      ->with(['messages' => function ($q) {
                          $q->where('by', 'Guest')
                            ->orderBy('created_at', 'desc')
                            ->limit(1); // Traemos solo el último mensaje
                      }]);
                },
                'hotel.user' => function ($q) {
                    $q->where('del', 0)
                      ->where('status', 1)
                      ->whereNotNull('periodicity_chat'); // Solo usuarios con periodicity_chat no nulo
                },
            ])
            ->select([
                'stays.id',
                'stays.updated_at',
                'stays.room',
                'stays.check_out',
                'stays.check_in',
                'stays.hotel_id',
                DB::raw("CASE
                            WHEN '$now' < DATE_FORMAT(stays.check_in, CONCAT('%Y-%m-%d ', COALESCE((SELECT checkin FROM hotels WHERE hotels.id = stays.hotel_id), '16:00'))) THEN 'pre-stay'
                            WHEN '$now' >= DATE_FORMAT(stays.check_in, CONCAT('%Y-%m-%d ', COALESCE((SELECT checkin FROM hotels WHERE hotels.id = stays.hotel_id), '16:00'))) AND '$now' < stays.check_out THEN 'in-stay'
                            WHEN '$now' >= stays.check_out AND '$now' THEN 'post-stay'
                         END as period")
            ]);

            // Filtramos por periodo
            if (!empty($filters['periods'])) {
                $query->havingRaw("period IN ('" . implode("','", $filters['periods']) . "')");
            }

            $stays = $query->orderBy('stays.updated_at', 'DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $result = $stays->map(function ($stay) use ($now) {
                $stayData = [
                    'stay_id' => $stay->id,
                    'room' => $stay->room,
                    'hotel_id' => $stay->hotel_id,
                    'period' => $stay->period,
                    'total_users' => $stay->hotel->user->count(),
                    'total_chats_pending' => $stay->chats->count(),
                    'users' => $stay->hotel->user->map(function ($user) use ($now, $stay) {
                        // Verificar periodicity_chat con el ultimo mensaje
                        $lastChatMessage = $stay->chats->flatMap->messages->first();
                        $lastMessageTime = $lastChatMessage ? Carbon::parse($lastChatMessage->created_at) : null;

                        // Validar si ha pasado el tiempo suficiente desde la última notificación
                        if ($lastMessageTime && $lastMessageTime->diffInMinutes($now) >= $user->periodicity_chat) {
                            // Verificamos si el usuario ha sido notificado recientemente
                            $lastNotified = $user->chat_last_notified_at ? Carbon::parse($user->chat_last_notified_at) : null;

                            // Verificamos si el usuario ha sido notificado recientemente
                            // Condiciones para enviar una nueva notificación:
                            // 1. Si nunca ha sido notigicado antes ($lastNotified es nulo).
                            // 2. Si ha sido notigicado antes, verificamos que el tiempo transcurrido desde la última notificación
                            //    sea mayor o iual a lo especificado en 'periodicity_chat'.
                            if (!$lastNotified || $lastNotified->diffInMinutes($now) >= $user->periodicity_chat) {
                                // Enviar pusher
                                sendEventPusher(
                                    'private-notify-unread-msg-hotel.' . $stay->hotel_id,
                                    'App\Events\NotifyUnreadMsg',
                                    [
                                        'user_id' => $user->id,
                                        'showLoadPage' => false,
                                        'guest_id' => $stay->guest_id ?? null,
                                        'stay_id' => $stay->id,
                                        'room' => $stay->room,
                                        'guest' => true,
                                        'text' => 'Tienes un chat sin responder',
                                        'automatic' => false,
                                        'add' => false,
                                        'pending' => false,
                                    ]
                                );

                                // Actualizamos el campo chat_last_notified_at del usuario
                                $user->update(['chat_last_notified_at' => $now]);

                                // usuario para log
                                return [
                                    'id' => $user->id,
                                    'name' => $user->name,
                                    'email' => $user->email,
                                    'periodicity_chat' => $user->periodicity_chat,
                                    'chat_last_notified_at' => $now,
                                ];
                            }
                        }

                        return null; // No enviar
                    })->filter(),

                    'pending_chats' => $stay->chats->map(function ($chat) {
                        $lastMessage = $chat->messages->first(); // Obtenemos el último mensaje enviado por 'Guest'
                        return [
                            'chat_id' => $chat->id,
                            'last_message' => $lastMessage ? $lastMessage->text : null, // Verificamos si hay mensaje
                            'last_message_by' => $lastMessage ? $lastMessage->by : null,
                            'last_message_status' => $lastMessage ? $lastMessage->status : null,
                            'LAST_MESSAGE_DATE' => $lastMessage->created_at ?? null,
                        ];
                    }),
                ];

                return $stayData;
            });

            // Log
            Log::info('Usuarios de Hoteles y Chats Pendientes con Último Mensaje: ' . json_encode([
                'stays' => $result,
                'total_count' => count($stays)
            ], JSON_PRETTY_PRINT));

        } catch (\Exception $e) {
            Log::error('Error al obtener los usuarios de los hoteles y chats pendientes', ['error' => $e->getMessage()]);
        }
    }




}
