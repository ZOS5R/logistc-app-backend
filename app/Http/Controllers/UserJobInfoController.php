<?php
namespace App\Http\Controllers;

use App\Models\UserJobInfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserJobInfoController extends Controller
{
    // Display job info
    public function index(Request $request)
    {
        $authUser = Auth::user();

        // If the user is a manager and a user_id is provided, fetch that user's info
        if ($authUser->role_id === 1 && $request->has('user_id')) {
            $userId = $request->user_id;
        } else {
            $userId = Auth::id();
        }

        $jobInfo = UserJobInfo::where('user_id', $userId)->first(); // Fetch a single record

        if (!$jobInfo) {
            return response()->json(['message' => 'No job information found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($jobInfo, Response::HTTP_OK);
    }


    // Store or update job info
    public function store(Request $request)
    {
        $authUser = Auth::user();

        if ($authUser->role_id === 1 && $request->has('user_id')) {
            $userId = $request->user_id;
        } else {
            $userId = Auth::id();
        }

        // Validate input
        $request->validate([
            'basic_salary' => 'required|numeric',
            'direct_manger_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $manager = User::where('id', $value)->where('role_id', 1)->first();
                    if (!$manager) {
                        $fail('The selected direct manager is not a valid manager.');
                    }
                }
            ],
            'employemnt_date' => 'required|date',
            'years_of_service' => 'required|integer|min:0',
            'department' => 'required|string|max:255',
        ]);

        // Check if job info exists
        $jobInfo = UserJobInfo::where('user_id', $userId)->first();

        if ($jobInfo) {
            $jobInfo->update($request->all());
            return response()->json($jobInfo, Response::HTTP_OK);
        }

        // Create new record if not found
        $data = $request->all();
        $data['user_id'] = $userId;

        $jobInfo = UserJobInfo::create($data);

        return response()->json($jobInfo, Response::HTTP_CREATED);
    }

    // Show job info
    public function show($id)
    {
        $authUser = Auth::user();
        $jobInfo = UserJobInfo::find($id);

        if (!$jobInfo) {
            return response()->json(['message' => 'Job information not found.'], Response::HTTP_NOT_FOUND);
        }

        // Only allow access if the user owns this record or is a manager
        if ($authUser->role_id !== 1 && $jobInfo->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        return response()->json($jobInfo, Response::HTTP_OK);
    }

    // Update job info
    public function update(Request $request, $id)
    {
        $authUser = Auth::user();
        $jobInfo = UserJobInfo::find($id);

        if (!$jobInfo) {
            return response()->json(['message' => 'Job information not found.'], Response::HTTP_NOT_FOUND);
        }

        // Only allow update if the user owns this record or is a manager
        if ($authUser->role_id !== 1 && $jobInfo->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'basic_salary' => 'required|numeric',
            'direct_manger_id' => 'nullable|exists:users,id',
            'employemnt_date' => 'required|date',
            'years_of_service' => 'required|integer|min:0',
            'department' => 'required|string|max:255',
        ]);

        $jobInfo->update($request->all());

        return response()->json($jobInfo, Response::HTTP_OK);
    }

    // Delete job info
    public function destroy($id)
    {
        $authUser = Auth::user();
        $jobInfo = UserJobInfo::find($id);

        if (!$jobInfo) {
            return response()->json(['message' => 'Job information not found.'], Response::HTTP_NOT_FOUND);
        }

        // Only allow deletion if the user owns this record or is a manager
        if ($authUser->role_id !== 1 && $jobInfo->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $jobInfo->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
