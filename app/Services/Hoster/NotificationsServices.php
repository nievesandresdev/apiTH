<?php

namespace App\Services\Hoster;

use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationsServices {

    function __construct(
        //
    )
    {
        //
    }

    public function maskAsReadToUser($userId)
    {
        try {
            return NotificationUser::where('user_id',$userId)
                    ->where('status','unread')
                    ->update(['status'=>'read']);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getNotificationsByUser($userId)
    {
        try {
            $user = User::select('id','created_at')->where('id',$userId)->first();
            $role = $user->getRoleNames()->first();

            $notifications_by_roles = Notification::with(['interaction' => function($q) use ($user){
                $q->where('notification_user.user_id', $user->id);}])
            ->whereHas('roles', function($q) use($role){
                $q->where('roles.name', $role);
                $q->where('notifications.type', 'news');
            });

            $notifications_by_users = Notification::with(['interaction' => function($q) use ($user){
                        $q->where('notification_user.user_id', $user->id);}])
                    ->whereHas('users', function($q) use($user){
                        $q->where('notification_user.user_id', $user->id);
                    });

            return $notifications_by_roles->union($notifications_by_users)
            ->where('created_at', '>=', $user->created_at )
            ->orderBy('id', 'desc')
            ->get();
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function vote($userId,  $noticationId, $face)
    {
        try {
            return NotificationUser::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'notification_id' => $noticationId
                ],
                [
                    'vote' => $face,
                    'status' => 'read',
                    'updated_at' => now()
                ]
            );
        } catch (\Exception $e) {
            return $e;
        }
    }

   



}
