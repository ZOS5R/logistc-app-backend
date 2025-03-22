<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized, manager only',
                'user'=>Auth::user()
            ], 403);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Log user details for debugging (avoid logging sensitive info in production)
        Log::info('User accessing protected function', ['user' => $user]);

        // Optionally, you can print user details directly using dd() for debugging:
        // dd($user);

        // Check if the user is a manager (assuming role_id 1 is for manager)
        if ($user->role_id !== 1) {
            return response()->json([
                'error' => 'Unauthorized, manager only',
                'role_id' => $user->role_id,
            ], 403);
        }

        return $next($request);
    }
}
