<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('driver.{driverId}', function ($user, $driverId) {
    // Here, verify if the authenticated user's id matches the driver id
    // For now, we allow any user to listen for testing purposes:
    return true;
});
