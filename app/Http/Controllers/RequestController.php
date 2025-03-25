<?php


namespace App\Http\Controllers;
use Illuminate\Routing\Controllers\HasMiddleware;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\Middleware;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class RequestController extends Controller implements HasMiddleware
{      public static function middleware()
    {
        return  [

           new MiddleWare('auth:sanctum', except: ['login'])];
    }
    // إنشاء طلب جديد
    public function store(HttpRequest $request)
    {
        $authUser = Auth::user(); // Get authenticated user

        $validated = $request->validate([
            'type' => 'required|in:loan,leave,extra_work,work_permit,other',
            'days' => 'nullable|integer',
            'hours' => 'nullable|integer',
            'amount' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $validated['user_id'] = $authUser->id;
        Request::create($validated);

        return response()->json(['message' => 'Request submitted successfully'], 201);
    }

    // عرض جميع الطلبات (للمدير فقط)
    public function index(Request $request)
    {
        $authUser = Auth::user(); // Get authenticated user

        if ($authUser->role_id == 1 && $request->has('user_id')) {
            $userId = $request->user_id;
        } else {
            $userId = $authUser->id; // Otherwise, use the authenticated user's ID
        }

        $requests = Request::with('user')->get();
        return response()->json($requests);
    }

    // عرض تفاصيل طلب معين
    public function show($id)
    {
        $req = Request::with('user')->findOrFail($id);
        return response()->json($req);
    }

    // تحديث حالة الطلب (للمدير فقط)
    public function updateStatus(HttpRequest $request, $id)
    {
        $req = Request::findOrFail($id);

        $authUser = Auth::user();

        // If manager provided user_id, use it; otherwise, use auth user
        if ($authUser->role_id == 1 && $request->has('user_id')) {
            $userId = $request->user_id;
        } else {
            $userId = $authUser->id;
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'note' => 'nullable|string',
            "user_id" => "required|exists:users,id"
        ]);
        $validated['user_id'] = $userId;
        $req->update($validated);
        return response()->json(['message' => 'Request status updated']);
    }

    // حذف الطلب (اختياري، يمكن تعطيله إن لم يكن مطلوبًا)
    public function destroy($id)
    {
        $authUser = Auth::user();
        $jobInfo = Request::find($id);

        if (!$jobInfo) {
            return response()->json(['message' => 'request information not found.'], Response::HTTP_NOT_FOUND);
        }

        // Only allow deletion if the user owns this record or is a manager
        if ($authUser->role_id !== 1 && $jobInfo->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $jobInfo->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
