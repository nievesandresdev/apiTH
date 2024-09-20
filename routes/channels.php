<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('noti-hotel.{id}', function ($hotel, $id) {
    return (int) $hotel->id === (int) $id;
});

Broadcast::channel('notify-send-query.{id}', function ($hotel, $id) {
    return (int) $hotel->id === (int) $id;
});

//stay
Broadcast::channel('update-stay-list-hotel.{id}', function ($hotel, $id) {
    return (int) $hotel->id === (int) $id;
});

Broadcast::channel('create-stay.{id}', function ($hotel, $id) {
    return (int) $hotel->id === (int) $id;
});

Broadcast::channel('update-chat.{id}', function ($guest, $id) {
    return (int) $guest->id === (int) $id;
});

Broadcast::channel('stay-sessions-hotel.{id}', function ($hotel, $id) {
    return (int) $hotel->id === (int) $id;
});

//chat
Broadcast::channel('notify-unread-msg-hotel.{id}', function ($hotel, $id) {
    return (int) $hotel->id === (int) $id;
});

Broadcast::channel('notify-unread-msg-guest.{id}', function ($guest, $id) {
    return (int) $guest->id === (int) $id;
});