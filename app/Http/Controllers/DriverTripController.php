<?php

namespace App\Http\Controllers;
use Pusher\Pusher;

use App\Events\DriverTripCreated;
use App\Models\DriverTrip;
use Illuminate\Http\Request;

use App\Models\AuctionModel;
use App\Models\Bid;
use App\Models\User;
 use Carbon\Carbon; // Make sure to use Carbon for time handling

use App\Models\Transaction; // Correct import for the Transaction model
use App\Models\StripeUserId; // Add this if you're also using the StripeUserId model
use Illuminate\Support\Facades\DB;
class DriverTripController extends Controller
{
    // Create a new driver trip and broadcast the event
    public function store(Request $request)
    {
        // Validate incoming data
        $data = $request->validate([
            'driver_token'         => 'required',
            'trip_description'  => 'required|string',
            'pick_up_latitude'  => 'required|numeric',
            'pick_up_longitude' => 'required|numeric',
            'drop_off_latitude' => 'required|numeric',
            'drop_off_longitude'=> 'required|numeric',
            // Status is optional here; default is pending
            'status'            => 'sometimes|string'
        ]);

        // Create the driver trip record in the database
        $driverTrip = DriverTrip::create($data);

        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options')
        );

        $pusher->trigger('ABC-app', "new-bid-". $driverTrip->id, $data);



        return response()->json($driverTrip, 201);
    }

    // Optional: Update the status of an existing trip (for example, when accepted)
    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|string|in:accepted,in_progress,completed,cancelled'
        ]);

        $driverTrip = DriverTrip::findOrFail($id);

        if ($data['status'] === 'accepted') {
            $driverTrip->markAccepted();
        } elseif ($data['status'] === 'completed') {
            $driverTrip->markCompleted();
        } else {
            $driverTrip->update(['status' => $data['status']]);
        }

        // Optionally broadcast an update event if needed

        return response()->json($driverTrip);
    }
}
