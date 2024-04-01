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

Broadcast::channel('create-stay.{id}', function ($hotel, $id) {
    return (int) $hotel->id === (int) $id;
});

Broadcast::channel('update-chat.{id}', function ($stay, $id) {
    return (int) $stay->id === (int) $id;
});

Broadcast::channel('noti-hotel.{id}', function ($hotel, $id) {
    return (int) $hotel->id === (int) $id;
});

Broadcast::channel('notify-send-query.{id}', function ($hotel, $id) {
    return (int) $hotel->id === (int) $id;
});