<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    // عرض جميع التقييمات مع بيانات المستخدم المرتبط
    public function index()
    {
        $evaluations = Evaluation::with('user')->get();
        return response()->json($evaluations);
    }

    // عرض تقييم معين بناءً على الـ ID
    public function show($id)
    {
        $evaluation = Evaluation::with('user')->findOrFail($id);
        return response()->json($evaluation);
    }

    // إنشاء تقييم جديد وتحديث النسبة الكلية
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'             => 'required|exists:users,id',
            'evaluation_date'     => 'required|date',
            'month'               => 'required|integer|min:1|max:12',
            'year'                => 'required|integer',
            'behavior_and_ethics' => 'required|string',
            'areas_of_improvement'=> 'required|string',
            'supervisor_notes'    => 'nullable|string',
            'monthly_percentage'  => 'nullable|string',
        ]);

        // إنشاء التقييم الجديد
        $evaluation = Evaluation::create($data);

        // حساب النسبة الكلية للمستخدم في السنة المحددة
        $overall = $this->calculateOverallPercentage($data['user_id'], $data['year']);
        $evaluation->overall_percentage = $overall;
        $evaluation->save();

        return response()->json($evaluation, 201);
    }

    // تحديث تقييم موجود وإعادة حساب النسبة الكلية
    public function update(Request $request, $id)
    {
        $evaluation = Evaluation::findOrFail($id);

        $data = $request->validate([
            'evaluation_date'     => 'sometimes|required|date',
            'month'               => 'sometimes|required|integer|min:1|max:12',
            'year'                => 'sometimes|required|integer',
            'behavior_and_ethics' => 'sometimes|required|string',
            'areas_of_improvement'=> 'sometimes|required|string',
            'supervisor_notes'    => 'nullable|string',
            'monthly_percentage'  => 'nullable|string',
        ]);

        $evaluation->update($data);

        // إعادة حساب النسبة الكلية للمستخدم في السنة المحددة
        $overall = $this->calculateOverallPercentage($evaluation->user_id, $evaluation->year);
        $evaluation->overall_percentage = $overall;
        $evaluation->save();

        return response()->json($evaluation);
    }

    // حذف تقييم
    public function destroy($id)
    {
        $evaluation = Evaluation::findOrFail($id);
        $evaluation->delete();
        return response()->json(['message' => 'Evaluation deleted successfully']);
    }

    // دالة لحساب النسبة الكلية لمستخدم معين خلال سنة محددة
    // تحسب هذه الدالة متوسط النسبة الشهرية لجميع التقييمات التي تحتوي على قيمة لنسبة الأداء الشهري
    public function calculateOverallPercentage($userId, $year)
    {
        $evaluations = Evaluation::where('user_id', $userId)
            ->where('year', $year)
            ->whereNotNull('monthly_percentage')
            ->get();

        if ($evaluations->isEmpty()) {
            return null;
        }

        $total = $evaluations->sum('monthly_percentage');
        $count = $evaluations->count();

        return round($total / $count, 2);
    }
}
