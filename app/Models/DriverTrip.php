<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverTrip extends Model
{
    protected $table = 'driver_trips';

    protected $fillable = [
        'driver_id',
        'trip_description',
        'pick_up_latitude',
        'pick_up_longitude',
        'drop_off_latitude',
        'drop_off_longitude',
        'status',
        'accepted_at',
        'completed_at'
    ];

    // Optionally, you can add methods to update the status
    public function markAccepted()
    {
        $this->update([
            'status'     => 'accepted',
            'accepted_at'=> now()
        ]);
    }

    public function markCompleted()
    {
        $this->update([
            'status'      => 'completed',
            'completed_at'=> now()
        ]);
    }
}
