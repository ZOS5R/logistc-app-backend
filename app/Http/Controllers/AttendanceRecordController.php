<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceRecordController extends Controller
{
    // Get all attendance records for the authenticated user
    public function index()
    {
        $user = Auth::user();
        $attendanceRecords = AttendanceRecord::where('user_id', $user->id)->get();

        return response()->json([
            'success' => true,
            'data'    => $attendanceRecords,
        ], 200);
    }

    // Create or update an attendance record
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate the request
        $request->validate([
            'clock_in'  => 'nullable|date_format:Y-m-d H:i:s',
            'clock_out' => 'nullable|date_format:Y-m-d H:i:s',
            'notes'     => 'nullable|string',
        ]);

        if (!$request->clock_in && !$request->clock_out) {
            return response()->json([
                'success' => false,
                'message' => 'Either clock_in or clock_out must be provided.'
            ], 400);
        }

        $today = Carbon::now()->toDateString();

        // Get today's schedule (set by the manager)
        $schedule = Schedule::where('user_id', $user->id)
                            ->whereDate('date', $today)
                            ->first();

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'No schedule found for today.'
            ], 404);
        }

        // Get or create the attendance record for today
        $attendanceRecord = AttendanceRecord::firstOrNew([
            'user_id' => $user->id,
            'date'    => $today,
        ]);

        $statusIn  = $attendanceRecord->status_in;
        $statusOut = $attendanceRecord->status_out;

        // Process clock-in logic
        if ($request->clock_in) {
            $clockInTime = Carbon::parse($request->clock_in);
            $scheduledClockIn = Carbon::parse($schedule->scheduled_clock_in);

            if ($clockInTime->equalTo($scheduledClockIn)) {
                $statusIn = 'on_time';
            } elseif ($clockInTime->lessThan($scheduledClockIn)) {
                $statusIn = 'early';
            } else {
                $statusIn = 'late';
            }

            $attendanceRecord->clock_in  = $clockInTime;
            $attendanceRecord->status_in = $statusIn;
        }

        // Process clock-out logic
        if ($request->clock_out) {
            $clockOutTime = Carbon::parse($request->clock_out);
            $scheduledClockOut = Carbon::parse($schedule->scheduled_clock_out);

            if ($clockOutTime->equalTo($scheduledClockOut)) {
                $statusOut = 'on_time';
            } elseif ($clockOutTime->lessThan($scheduledClockOut)) {
                $statusOut = 'early';
            } else {
                $statusOut = 'late';
            }

            $attendanceRecord->clock_out  = $clockOutTime;
            $attendanceRecord->status_out = $statusOut;
        }

        // Save the record
        $attendanceRecord->notes = $request->notes ?? $attendanceRecord->notes;
        $attendanceRecord->save();

        return response()->json([
            'success' => true,
            'message' => 'Attendance record saved successfully.',
            'data'    => $attendanceRecord,
        ], 201);
    }

    // Update an existing attendance record manually
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'clock_in'  => 'nullable|date_format:Y-m-d H:i:s',
            'clock_out' => 'nullable|date_format:Y-m-d H:i:s',
            'notes'     => 'nullable|string',
        ]);

        $attendanceRecord = AttendanceRecord::where('user_id', $user->id)->findOrFail($id);

        $attendanceRecord->update([
            'clock_in'  => $request->clock_in ?? $attendanceRecord->clock_in,
            'clock_out' => $request->clock_out ?? $attendanceRecord->clock_out,
            'notes'     => $request->notes ?? $attendanceRecord->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance record updated successfully.',
            'data'    => $attendanceRecord,
        ], 200);
    }

    // Delete an attendance record
    public function destroy($id)
    {
        $user = Auth::user();
        $attendanceRecord = AttendanceRecord::where('user_id', $user->id)->findOrFail($id);
        $attendanceRecord->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance record deleted successfully.',
        ], 200);
    }
}
