<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Stay;

class PeriodicityChat extends Command
{
    protected $signature = 'app:periodicity-chat';
    protected $description = 'Chat y feedback pendientes según periodicidad de los usuarios de los hoteles.';

    public function handle()
    {
        $filters = ['periods' => ['in-stay', 'pre-stay']];

        try {
            $now = Carbon::now();
            $limit = $filters['limit'] ?? 10;
            $offset = $filters['offset'] ?? 0;

            $query = Stay::with([
                'hotel.user' => function ($q) {
                    $q->where('del', 0)
                        ->where('status', 1)
                        ->whereNotNull('periodicity_chat')
                        ->whereNotNull('periodicity_stay');
                },
                'queries' => function ($q) {
                    $q->where('answered', 1)
                        ->where('attended', 0);
                },
            ])
            ->whereHas('chats', function ($q) {
                $q->where('pending', 1)
                    ->with(['messages' => function ($q) {
                        $q->where('by', 'Guest')
                            ->orderBy('created_at', 'desc')
                            ->limit(1);
                    }]);
            })
            ->orWhereHas('queries', function ($q) {
                $q->where('answered', 1)
                    ->where('attended', 0);
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
                            WHEN '$now' >= stays.check_out THEN 'post-stay'
                        END as period")
            ]);

            if (!empty($filters['periods'])) {
                $query->havingRaw("period IN ('" . implode("','", $filters['periods']) . "')");
            }

            $stays = $query->orderBy('stays.updated_at', 'DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $stays->each(function ($stay) use ($now) {
                $stay->hotel->user->each(function ($user) use ($stay, $now) {
                    $notifications = is_string($user->notifications)
                        ? json_decode($user->notifications, true) ?? []
                        : ($user->notifications ?? []);

                    $chatLastNotified = is_string($user->chat_last_notified_at)
                        ? json_decode($user->chat_last_notified_at, true) ?? []
                        : ($user->chat_last_notified_at ?? []);

                    $feedbackLastNotified = is_string($user->feedback_last_notified_at)
                        ? json_decode($user->feedback_last_notified_at, true) ?? []
                        : ($user->feedback_last_notified_at ?? []);

                    $periodicityChat = is_string($user->periodicity_chat)
                        ? json_decode($user->periodicity_chat, true) ?? []
                        : ($user->periodicity_chat ?? []);

                    $periodicityFeedback = is_string($user->periodicity_stay)
                        ? json_decode($user->periodicity_stay, true) ?? []
                        : ($user->periodicity_stay ?? []);

                    // Verificar notificaciones de chats
                    foreach (['pendingChat10', 'pendingChat30'] as $type) {

                        // Push notification
                        if (($notifications['push'][$type] ?? false) && isset($periodicityChat[$type])) {
                            $lastMessage = $stay->chats->flatMap->messages->first();
                            $lastMessageTime = $lastMessage ? Carbon::parse($lastMessage->created_at) : null;

                            if (
                                $lastMessageTime &&
                                $lastMessageTime->diffInMinutes($now) >= $periodicityChat[$type] &&
                                (!isset($chatLastNotified[$type]) || Carbon::parse($chatLastNotified[$type])->diffInMinutes($now) >= $periodicityChat[$type])
                            ) {

                                sendEventPusher(
                                    'private-notify-unread-msg-hotel.' . $stay->hotel_id,
                                    'App\Events\NotifyUnreadMsg',
                                    [
                                        'user_id' => $user->id,
                                        'showLoadPage' => false,
                                        'guest_id' => $stay->chats->first()?->guest_id ?? null,
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

                                $chatLastNotified[$type] = $now->toDateTimeString();
                                $user->update(['chat_last_notified_at' => $chatLastNotified]);


                            }
                        }


                    }

                    // Verificar notificaciones de feedback
                    foreach (['pendingFeedback30', 'pendingFeedback60'] as $type) {
                        // Push notification
                        if (($notifications['push'][$type] ?? false) && isset($periodicityFeedback[$type])) {
                            if (
                                !isset($feedbackLastNotified[$type]) ||
                                Carbon::parse($feedbackLastNotified[$type])->diffInMinutes($now) >= $periodicityFeedback[$type]
                            ) {
                                sendEventPusher('notify-send-query.' . $stay->hotel_id, 'App\Events\NotifySendQueryEvent', [
                                    "userId" => $user->id,
                                    "stayId" => $stay->id,
                                    "guestId" => $stay->queries->first()?->guest_id ?? null,
                                    "title" => "Feedback Pendiente",
                                    "text" => "Tienes un Feedback pendiente",
                                    "concept" => "pending",
                                    "countPendingQueries" => 1,
                                ]);

                                $feedbackLastNotified[$type] = $now->toDateTimeString();
                                $user->update(['feedback_last_notified_at' => $feedbackLastNotified]);
                            }
                        }
                    }
                });
            });

            Log::info('Finalizado PeriodicityChat con éxito.');
        } catch (\Exception $e) {
            Log::error('Error al obtener los usuarios de los hoteles y chats/queries pendientes', ['error' => $e->getMessage()]);
        }
    }
}
