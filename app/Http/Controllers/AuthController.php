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
use Illuminate\Support\Facades\DB;

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
            'email' => 'required_without:token',
            'password' => 'required',
            'token' => 'required_without:email',
        ]);

        // Case 1: Login with email and password
        if ($request->has('email') && $request->email !== null) {
            // Check if user exists
            $user = User::where('email', $request->email)->first();

            // If no user exists
            if (!$user) {
                Log::error("Login failed for email: ", ['email' => $request->email]);
                return response()->json(['message' => 'User not found', 'status_code' => 404]);
            }

            // If password is incorrect
            if (!Hash::check($request->password, $user->password)) {
                Log::error("Login failed for email: ", ['email' => $request->email]);
                return response()->json(['message' => 'Incorrect password', 'status_code' => 401]);
            }

            // Generate token without the user ID prefix
            $token = $user->createToken('YourAppName')->plainTextToken;

            // Strip out the user ID from the token if present
            $tokenParts = explode('|', $token);
            $plainToken = $tokenParts[1];  // The actual token without the user ID

            // Fetch user information (full name and image) from user_information table
            $userInfo = DB::table('user_information')->where('user_id', $user->id)->first();

            // Return response with token and additional user info
            return response()->json([
                'token' => $plainToken,
                'role_id' => $user->role_id,
                'status_code' => 200,
                'message' => "Login successful",
                'full_name' => $userInfo->full_name ?? 'N/A',  // Get full name, default to 'N/A' if not found
                'image' => $userInfo->image ?? null,  // Get image, default to null if not found
            ]);
        }

        // Case 2: Login with token and password
        if ($request->has('token')) {
            $token = $request->token;

            // Strip out the user ID from the token if present (in case it's in the format id|token)
            $tokenParts = explode('|', $token);
            $plainToken = $tokenParts[1] ?? $token;  // The actual token part

            // Log the token you're checking
            Log::info("Checking token: ", ['hashed_token' => hash('sha256', $plainToken)]);

            // Find the user by token in the personal_access_tokens table directly
            $tokenRecord = DB::table('personal_access_tokens')
                ->where('token', hash('sha256', $plainToken)) // Hash the token as it's stored hashed
                ->first();

            // Log the token record found
            Log::info("Token record found in DB: ", ['token_record' => $tokenRecord]);

            // If no token record exists
            if (!$tokenRecord) {
                Log::error("Login failed for token: ", ['token' => $plainToken]);
                return response()->json(['message' => 'Invalid token', 'status_code' => 401]);
            }

            // Find the user associated with the token
            $user = User::find($tokenRecord->tokenable_id);

            // If no user exists
            if (!$user) {
                Log::error("Login failed for token: ", ['token' => $plainToken]);
                return response()->json(['message' => 'User not found', 'status_code' => 404]);
            }

            // If password is incorrect
            if (!Hash::check($request->password, $user->password)) {
                Log::error("Login failed for token: ", ['token' => $plainToken]);
                return response()->json(['message' => 'Incorrect password', 'status_code' => 401]);
            }

            // Fetch user information (full name and image) from user_information table
            $userInfo = DB::table('user_information')->where('user_id', $user->id)->first();

            // Return response with token and additional user info
            return response()->json([
                'token' => $plainToken,
                'role_id' => $user->role_id,
                'status_code' => 200,
                'message' => "Login successful",
                'full_name' => $userInfo->full_name ?? 'N/A',  // Get full name, default to 'N/A' if not found
                'image' => $userInfo->image ?? null,  // Get image, default to null if not found
            ]);
        }

        // Default case: if neither email nor token is provided
        return response()->json(['message' => 'Either email or token is required', 'status_code' => 400]);
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
            'password' => 'required|string|min:6',
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



