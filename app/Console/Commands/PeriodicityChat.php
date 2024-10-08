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
    protected $description = 'chat y feedback pendientes segun periodicidad de los usuarios de los hoteles';

    /**
     * Execute the console command.
     */

     public function handle()
    {
        //Log::info('Inicio de la tarea programada app:periodicity-chat');
        $filters = ['periods' => ['in-stay', 'pre-stay']];

        try {
            $now = Carbon::now();
            $limit = $filters['limit'] ?? 10;
            $offset = $filters['offset'] ?? 0;

            // Definimos las relaciones y los campos a seleccionar
            $query = Stay::with([
                'hotel.user' => function ($q) {
                    $q->where('del', 0)
                    ->where('status', 1)
                    ->whereNotNull('periodicity_chat') // Solo usuarios con periodicity_chat no nulo
                    ->whereNotNull('periodicity_stay'); // Solo usuarios con periodicity_stay no nulo
                },
                // Relación para traer queries respondidas pero no atendidas
                'queries' => function ($q) {
                    $q->where('answered', 1)   // Respondida por el huésped
                    ->where('attended', 0);  // No atendida por el hoster
                },
            ])
            ->whereHas('chats', function ($q) {
                $q->where('pending', 1)
                ->with(['messages' => function ($q) {
                    $q->where('by', 'Guest')
                        ->orderBy('created_at', 'desc')
                        ->limit(1); // Traemos solo el último mensaje
                }]);
            })
            ->orWhereHas('queries', function ($q) {
                $q->where('answered', 1)
                ->where('attended', 0);  // No atendida por el hoster
            })
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

            // Procesamos los resultados
            $result = $stays->map(function ($stay) use ($now) {
                // Log::info('$stay '. json_encode($stay));
                $firstPendingChat = $stay->chats()->where('pending',1)->first();
                $firstguestIdChat = $firstPendingChat->guest_id ?? null;
                // Log::info('firstguestIdChat '. $firstguestIdChat);
                $firstPendingQuery = $stay->queries()->where('answered', 1)->where('attended', 0)->first();
                $firstguestIdQuery = $firstPendingQuery->guest_id ?? null;
                // Log::info('firstguestIdQuery '. $firstguestIdQuery);
                $stayData = [
                    'stay_id' => $stay->id,
                    'room' => $stay->room,
                    'hotel_id' => $stay->hotel_id,
                    'period' => $stay->period,
                    'total_users' => $stay->hotel->user->count(),
                    'total_chats_pending' => $stay->chats->count(),
                    'total_queries_pending' => $stay->queries->count(), // Total de queries pendientes
                    'users' => $stay->hotel->user->map(function ($user) use ($now, $stay, $firstguestIdChat, $firstguestIdQuery) {

                        // Verificar periodicity_chat con el último mensaje
                        $lastChatMessage = $stay->chats->flatMap->messages->first();
                        $lastMessageTime = $lastChatMessage ? Carbon::parse($lastChatMessage->created_at) : null;

                        // Validar si ha pasado el tiempo suficiente desde la última notificación de chat
                        if ($lastMessageTime && $lastMessageTime->diffInMinutes($now) >= $user->periodicity_chat) {
                            // Verificamos si el usuario ha sido notificado recientemente
                            $lastNotified = $user->chat_last_notified_at ? Carbon::parse($user->chat_last_notified_at) : null;

                            if (!$lastNotified || $lastNotified->diffInMinutes($now) >= $user->periodicity_chat) {
                                // Enviar pusher para chats pendientes

                                sendEventPusher(
                                    'private-notify-unread-msg-hotel.' . $stay->hotel_id,
                                    'App\Events\NotifyUnreadMsg',
                                    [
                                        'user_id' => $user->id,
                                        'showLoadPage' => false,
                                        'guest_id' => $firstguestIdChat,
                                        'stay_id' => $stay->id,
                                        'room' => $stay->room,
                                        'guest' => true,
                                        'text' => 'Tienes un chat sin responder',
                                        'automatic' => false,
                                        'add' => false,
                                        'pending' => false,
                                        'concept' => "pending",
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

                        // Verificar periodicity_stay para enviar notificación de feedback
                        $lastFeedbackNotified = $user->feedback_last_notified_at ? Carbon::parse($user->feedback_last_notified_at) : null;

                        // Si nunca ha sido notificado sobre feedback o ha pasado suficiente tiempo según periodicity_stay
                        if (!$lastFeedbackNotified || $lastFeedbackNotified->diffInMinutes($now) >= $user->periodicity_stay) {
                            // Enviar pusher para feedback pendiente
                            sendEventPusher('notify-send-query.' . $stay->hotel_id, 'App\Events\NotifySendQueryEvent',
                                [
                                    "userId" => $user->id,
                                    "stayId" => $stay->id,
                                    "guestId" => $firstguestIdQuery,
                                    "title" => "Feedback Pendiente",
                                    "text" => "Tienes un Feedback pendiente",
                                    "concept" => "pending",
                                    "countPendingQueries" => 1
                                ]
                            );

                            // Actualizamos el campo feedback_last_notified_at del usuario
                            $user->update(['feedback_last_notified_at' => $now]);
                        }

                        return null; // No enviar si no se cumplen las condiciones
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

                    'pending_queries' => $stay->queries->map(function ($query) {
                        return [
                            'query_id' => $query->id,
                            'comment' => $query->comment,
                            'answered' => $query->answered,
                            'attended' => $query->attended,
                            'responded_at' => $query->responded_at,
                        ];
                    }),
                ];

                return $stayData;
            });

            // Log
            Log::info('Usuarios de Hoteles y Chats Pendientes con Último Mensaje y Queries: ' . json_encode([
                'stays' => $result,
                'total_count' => count($stays)
            ], JSON_PRETTY_PRINT));

        } catch (\Exception $e) {
            Log::error('Error al obtener los usuarios de los hoteles y chats/queries pendientes', ['error' => $e->getMessage()]);
        }
    }

public function handle2()
    {
        $filters = ['periods' => ['in-stay', 'pre-stay']];

        try {
            $now = Carbon::now();
            $limit = $filters['limit'] ?? 10;
            $offset = $filters['offset'] ?? 0;

            $query = Stay::with([
                /* 'chats' => function ($q) {
                    $q->where('pending', 1)
                      ->with(['messages' => function ($q) {
                          $q->where('by', 'Guest')
                            ->orderBy('created_at', 'desc')
                            ->limit(1); // Traemos solo el último mensaje
                      }]);
                }, */
                'hotel.user' => function ($q) {
                    $q->where('del', 0)
                      ->where('status', 1)
                      ->whereNotNull('periodicity_chat'); // Solo usuarios con periodicity_chat no nulo
                },
            ])
            ->whereHas('chats', function ($q) {
                $q->where('pending', 1)
                  ->with(['messages' => function ($q) {
                      $q->where('by', 'Guest')
                        ->orderBy('created_at', 'desc')
                        ->limit(1); // Traemos solo el último mensaje
                  }]);
            })
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
                                        'concept' => "pending",
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
