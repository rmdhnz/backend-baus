<?php 

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('staff-im.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});