<?php

namespace App\Http\Controllers;

use App\Models\Redemption;
use App\Models\RewardItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class RedemptionController extends Controller implements HasMiddleware
{      public static function middleware()
    {
        return  [

           new MiddleWare('auth:sanctum', except: ['login'])];
    }
    // إنشاء طلب ج

    // عرض سجل الاستبدالات؛ المدير يرى الكل، والمستخدم العادي يشوف طلباته فقط
    public function index(Request $request)
    {
        $authUser = Auth::user();

        // إذا كان المدير (نفترض أن role_id==1 يعني المدير) ويمكنه تمرير user_id لاستعراض الكل
        if ($authUser->role_id == 1 && $request->has('user_id')) {
            $redemptions = Redemption::with('user', 'rewardItem')->get();
        } else {
            $redemptions = Redemption::with('rewardItem')
                ->where('user_id', $authUser->id)
                ->get();
        }

        return response()->json($redemptions);
    }

    public function getUserRedemptionsWithPoints()
{
    $user = Auth::user();


    // جلب جميع عمليات الاستبدال للمستخدم مع تفاصيل العنصر المستبدل
    $redemptions = Redemption::with('rewardItem')
        ->where('user_id', $user->id)
        ->get();

    // جلب نقاط المستخدم
    $points = $user->points;

    return response()->json([
        'points' => $points,
        'redemptions' => $redemptions
    ]);
}


    // تقديم طلب استبدال
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reward_item_id' => 'required|exists:reward_items,id',
        ]);

        $user = Auth::user();
        $rewardItem = RewardItem::findOrFail($validated['reward_item_id']);

        // التحقق من وجود نقاط كافية
        if ($user->points < $rewardItem->points_cost) {
            return response()->json(['message' => 'Not enough points'], 400);
        }

        // تنفيذ العملية داخل معاملة لضمان التكامل
        DB::transaction(function () use ($user, $rewardItem) {
            $user->points -= $rewardItem->points_cost;
            $user->save();

            // إنشاء سجل الاستبدال
            Redemption::create([
                'user_id' => $user->id,
                'reward_item_id' => $rewardItem->id,
                'status' => 'pending',
            ]);

            // تسجيل العملية في سجل النقاط
            DB::table('points_transactions')->insert([
                'user_id'     => $user->id,
                'points_change'      => -$rewardItem->points_cost,
                'reason' => 'Redeemed reward: ' . $rewardItem->name,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });

        // Ensure to return the updated points after transaction
        return response()->json([
            'message' => 'Redemption request submitted successfully',
            'user_points' => $user->points, // The points after the decrement
        ], 201);
    }



    // تحديث حالة طلب الاستبدال (للمدير فقط)
    public function updateStatus(Request $request, $id)
    {
        $authUser = Auth::user();

        // تحقق من صلاحية المدير
        if ($authUser->role_id != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $redemption = Redemption::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'note' => 'nullable|string',
        ]);

        $redemption->status = $validated['status'];
        if (isset($validated['note'])) {
            $redemption->note = $validated['note'];
        }

        // عند الموافقة، يتم خصم النقاط من رصيد المستخدم
        if ($validated['status'] === 'approved') {
            $user = $redemption->user;
            $pointsCost = $redemption->rewardItem->points_cost;

            // التحقق مجددًا من وجود نقاط كافية (قد يكون المستخدم أنفق نقاطه في طلبات أخرى)
            if ($user->points < $pointsCost) {
                return response()->json(['message' => 'User does not have enough points'], 400);
            }

            DB::transaction(function () use ($user, $redemption, $pointsCost) {
                $user->decrement('points', $pointsCost);
                $redemption->save();
            });
        } else {
            $redemption->save();
        }

        return response()->json(['message' => 'Redemption status updated']);
    }

    // حذف طلب الاستبدال (يمكن للمستخدم حذف طلباته إذا كانت لم تُعالج بعد)
    public function destroy($id)
    {
        $redemption = Redemption::findOrFail($id);
        $authUser = Auth::user();

        if ($authUser->id !== $redemption->user_id && $authUser->role_id != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // يمكن حذف الطلب فقط إذا كان في حالة "pending"
        if ($redemption->status !== 'pending') {
            return response()->json(['message' => 'Cannot delete processed redemption'], 400);
        }

        $redemption->delete();
        return response()->json(['message' => 'Redemption deleted']);
    }
}
