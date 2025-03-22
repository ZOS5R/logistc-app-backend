<?php
namespace App\Http\Controllers;

use App\Models\UserInformation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserInformationController extends Controller
{
    // Display a listing of the resource
    public function index(Request $request)
    {
        $authUser = Auth::user(); // Get authenticated user

        // If the user is a manager and a user_id is provided, get that user's information
        if ($authUser->role_id == 1 && $request->has('user_id')) {
            $userId = $request->user_id;
        } else {
            $userId = $authUser->id; // Otherwise, use the authenticated user's ID
        }

        $userInformations = UserInformation::where('user_id', $userId)->get();

        if ($userInformations->isEmpty()) {
            return response()->json(['message' => 'No user information found. Please set your information.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($userInformations, Response::HTTP_OK);
    }

    // Store or update a user’s information
    public function store(Request $request)
    {
        $authUser = Auth::user();

        // If manager provided user_id, use it; otherwise, use auth user
        if ($authUser->role_id == 1 && $request->has('user_id')) {
            $userId = $request->user_id;
        } else {
            $userId = $authUser->id;
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'position' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'nationality' => 'required|string|max:255',
            'marital_status' => 'required|string|max:255',
        ]);

        // Check if user information already exists
        $userInformation = UserInformation::where('user_id', $userId)->first();

        $data = $request->except('image');
        $data['user_id'] = $userId;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('media/user_images'), $imageName);
            $data['image'] = $imageName;
        }

        if ($userInformation) {
            $userInformation->update($data);
            return response()->json($userInformation, Response::HTTP_OK);
        }

        $userInformation = UserInformation::create($data);
        return response()->json($userInformation, Response::HTTP_CREATED);
    }

    // Show a user's information
    public function show(Request $request, UserInformation $userInformation)
    {
        $authUser = Auth::user();

        if ($authUser->role_id != 1 && $authUser->id !== $userInformation->user_id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        return response()->json($userInformation, Response::HTTP_OK);
    }

    // Update a user's information
    public function update(Request $request, UserInformation $userInformation)
    {
        $authUser = Auth::user();

        // If manager, allow updating another user's info
        if ($authUser->role_id == 1 && $request->has('user_id')) {
            $userId = $request->user_id;
        } else {
            $userId = $authUser->id;
        }

        if ($userInformation->user_id != $userId) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'position' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'nationality' => 'required|string|max:255',
            'marital_status' => 'required|string|max:255',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            if ($userInformation->image) {
                unlink(public_path('media/user_images/' . $userInformation->image));
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('media/user_images'), $imageName);
            $data['image'] = $imageName;
        }

        $userInformation->update($data);

        return response()->json($userInformation, Response::HTTP_OK);
    }

    // Delete a user's information
    public function destroy(Request $request, UserInformation $userInformation)
    {
        $authUser = Auth::user();

        // If manager, allow deleting another user's info
        if ($authUser->role_id == 1 && $request->has('user_id')) {
            $userId = $request->user_id;
        } else {
            $userId = $authUser->id;
        }

        if ($userInformation->user_id != $userId) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        if ($userInformation->image) {
            unlink(public_path('media/user_images/' . $userInformation->image));
        }

        $userInformation->delete();

        return response()->json(['message' => 'User information deleted'], Response::HTTP_NO_CONTENT);
    }
}
