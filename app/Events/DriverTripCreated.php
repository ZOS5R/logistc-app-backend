<?php

namespace App\Events;

use App\Models\DriverTrip;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverTripCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driverTrip;

    public function __construct(DriverTrip $driverTrip)
    {
        $this->driverTrip = $driverTrip;
    }

    // Broadcast on a private channel for the specific driver
    public function broadcastOn()
    {
        return new PrivateChannel('driver.' . $this->driverTrip->driver_id);
    }

    // Optionally define a custom event name for the frontend
    public function broadcastAs()
    {
        return 'DriverTripCreated';
    }
}
