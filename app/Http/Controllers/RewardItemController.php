<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\HasMiddleware;
use App\Models\Redemption;
use App\Models\RewardItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\Middleware;

class RewardItemController extends Controller implements HasMiddleware
{      public static function middleware()
    {
        return  [

           new MiddleWare('auth:sanctum', except: ['login'])];
    }

    // عرض سجل الاستبدالات؛ المدير يرى الكل، والمستخدم العادي يرى طلباته فقط
    public function index(Request $request)
    {
        $authUser = Auth::user();

        if ($authUser->role_id == 1 && $request->has('user_id')) {
            $redemptions = Redemption::with('user', 'rewardItem')->get();
        } else {
            $redemptions = Redemption::with('rewardItem')
                ->where('user_id', $authUser->id)
                ->get();
        }

        return response()->json($redemptions);
    }

    // تقديم طلب استبدال مع خصم النقاط
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
            $user->points =500;
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
                'change'      => -$rewardItem->points_cost,
                'description' => 'Redeemed reward: ' . $rewardItem->name,
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


}
