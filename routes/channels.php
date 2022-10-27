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

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

// Broadcast::channel('users.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });


// Broadcast::channel('test', function () {
//     // return (int) $user->id === (int) $id;
//     return ['chann' => 'test', 'test' => true];
// });

// Broadcast::channel('gaming-test', function ($user) {
//     // return (int) $user->id === (int) $id;
//     return ['id' => $user->id, 'name' => $user->fullname];
// });

// Broadcast::channel('chat',function($user){
//     // return $user;
//     return ['chann' => 'chat', 'chat' => true, 'user' => $user];
// });

// Broadcast::channel('public', function () {
//     return true;
// });

// Broadcast::channel('private.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

// // Broadcast::channel('presence.{groupId}', function ($user,int $groupId) {
// Broadcast::channel('presence.{groupId}', function ($user, int $groupId) {
//     // if ($user->canJoinGroup($groupId)) {
//         return ['id' => $user->id, 'name' => $user->name, 'qwerty' => 'abcde', 'groupId' => $groupId];
//     // }
// });
