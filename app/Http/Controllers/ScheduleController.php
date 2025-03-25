<?php
namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    // عرض جميع الجداول
    public function index()
    {
        $schedules = Schedule::all();
        return view('schedules.index', compact('schedules'));
    }

    // عرض نموذج إضافة جدول
    public function create()
    {
        $users = User::all(); // عرض جميع المستخدمين لاختيارهم
        return view('schedules.create', compact('users'));
    }

    // حفظ الجدول الجديد
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'scheduled_clock_in' => 'required|date_format:H:i',
            'scheduled_clock_out' => 'required|date_format:H:i',
        ]);

        Schedule::create([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'scheduled_clock_in' => $request->scheduled_clock_in,
            'scheduled_clock_out' => $request->scheduled_clock_out,
        ]);

        return redirect()->route('schedules.index')->with('success', 'Schedule created successfully');
    }

    // عرض نموذج تعديل الجدول
    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        $users = User::all();
        return view('schedules.edit', compact('schedule', 'users'));
    }

    // تحديث الجدول
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'scheduled_clock_in' => 'required|date_format:H:i',
            'scheduled_clock_out' => 'required|date_format:H:i',
        ]);

        $schedule = Schedule::findOrFail($id);
        $schedule->update([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'scheduled_clock_in' => $request->scheduled_clock_in,
            'scheduled_clock_out' => $request->scheduled_clock_out,
        ]);

        return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully');
    }

    // حذف الجدول
    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('schedules.index')->with('success', 'Schedule deleted successfully');
    }
}
