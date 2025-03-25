<?php
namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Pusher\Pusher;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    // استرجاع كافة الرحلات الخاصة بالسائق مع الفلاتر
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصادق، يرجى تسجيل الدخول'
            ], 401);
        }

        // الحصول على الفلتر المطلوب
        $statusFilter = $request->input('trip_state'); // يمكن أن يكون 'current' أو 'completed' أو 'future'
        $query = Trip::where('driver_id', operator: $user->id);

        // إضافة الفلاتر بناءً على الحالة
        if ($statusFilter) {
            if ($statusFilter === 'current') {
                // الرحلات الحالية (التي بدأت ولكن لم تكتمل بعد)
                $query->whereNull('completed_at')->where('start_trip_at', '<=', now());
            } elseif ($statusFilter === 'completed') {
                // الرحلات المكتملة (التي تم إتمامها)
                $query->whereNotNull('completed_at');
            } elseif ($statusFilter === 'future') {
                // الرحلات المستقبلية (التي لم تبدأ بعد)
                $query->where('trip_date', '>', now());
            }
        }

        // جلب الرحلات مع الترحيل
        $trips = $query->paginate(15);

        return response()->json([
            'status'       => true,
            'message'      => 'تم جلب الرحلات بنجاح',
            'current_page' => $trips->currentPage(),
            'last_page'    => $trips->lastPage(),
            'total'        => $trips->total(),
            'data'         => $trips->items(), // فقط البيانات بدون تفاصيل الترحيل الكاملة
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'driver_id'         => 'required|integer',
            'trip_description'  => 'required|string|max:255',
            'pick_up_latitude'  => 'required|numeric',
            'pick_up_longitude' => 'required|numeric',
            'drop_off_latitude' => 'required|numeric',
            'drop_off_longitude'=> 'required|numeric',
            'status'            => 'required|string|in:pending,started,completed',
            'trip_date'         => 'nullable|date', // New: Can be NULL or a specific date
            'start_trip_at'     => 'nullable|date',
            'load_at'           => 'nullable|date',
            'arrival_at'        => 'nullable|date',
            'unload_at'         => 'nullable|date',
            'accepted_at'       => 'nullable|date',
            'completed_at'      => 'nullable|date'
        ]);

        // Set default trip_date to today if NULL
        $tripDate = $data['trip_date'] ?? now()->toDateString();

        // Determine trip type
        if ($data['status'] === 'completed') {
            $tripType = 'completed';
        } elseif ($tripDate > now()->toDateString()) {
            $tripType = 'future';
        } else {
            $tripType = 'current';
        }

        // Add trip type to data array
        $data['trip_type'] = $tripType;

        // Create the trip
        $trip = Trip::create($data);

        // Send a push notification **only if the trip is for today**
        if ($tripType === 'current') {
            // Get the driver's latest token
            $driverTokenRecord = DB::table('personal_access_tokens')
                ->where('tokenable_id', $data['driver_id'])
                ->first();

            $driverToken = $driverTokenRecord ? $driverTokenRecord->token : 'default-token';

            // Pusher setup
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );

            // Emit event only for current trips
            $eventName = "new-trip-" . $driverToken;
            $pusher->trigger('ABC-app', $eventName, $data);
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم إنشاء الرحلة بنجاح',
            'trip'    => $trip
        ], 201);
    }


    // استرجاع بيانات رحلة معينة
    public function show($id)
    {
        $trip = Trip::findOrFail($id);
        return response()->json($trip);
    }

    // تحديث بيانات رحلة موجودة
    public function update(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);

        $data = $request->validate([
            'driver_id'         => 'sometimes|required|integer',
            'trip_description'  => 'sometimes|required|string|max:255',
            'pick_up_latitude'  => 'sometimes|required|numeric',
            'pick_up_longitude' => 'sometimes|required|numeric',
            'drop_off_latitude' => 'sometimes|required|numeric',
            'drop_off_longitude'=> 'sometimes|required|numeric',
            'status'            => 'sometimes|required|string',
            'start_trip_at'     => 'nullable|date',
            'load_at'           => 'nullable|date',
            'arrival_at'        => 'nullable|date',
            'unload_at'         => 'nullable|date',
            'accepted_at'       => 'nullable|date',
            'completed_at'      => 'nullable|date'
        ]);

        $trip->update($data);
        return response()->json($trip);
    }

    // حذف رحلة
    public function destroy($id)
    {
        $trip = Trip::findOrFail($id);
        $trip->delete();
        return response()->json(['message' => 'تم حذف الرحلة بنجاح']);
    }
}
