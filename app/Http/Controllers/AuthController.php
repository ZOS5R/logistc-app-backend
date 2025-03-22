<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return  [

           new MiddleWare('auth:sanctum', except: ['login'])];
    }
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if user exists
        $user = User::where('email', $request->email)->first();

        // If no user exists
        if (!$user) {
            Log::error("Login failed for: ", ['email' => $request->email]);
            return response()->json(['error' => 'User not found'], 404);
        }

        // If password is incorrect
        if (!Hash::check($request->password, $user->password)) {
            Log::error("Login failed for: ", ['email' => $request->email]);
            return response()->json(['error' => 'Incorrect password'], 401);
        }

        // Generate token
        $token = $user->createToken('YourAppName')->plainTextToken;

        // Return response with token
        return response()->json(['token' => $token]);
    }


    // Register method (only for manager)
    public function register(Request $request)
    {
        // Check if the logged-in user is a manager (role_id 1)
        $authenticatedUser = Auth::user();

        if (!$authenticatedUser || $authenticatedUser->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 403); // Not a manager
        }

        // Validate input data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email', // Ensure email is unique
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Check if the email already exists
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            return response()->json(['error' => 'User with this email already exists'], 409); // Conflict (409)
        }

        // Create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id, // Assign role
        ]);

        // Return response
        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

}



