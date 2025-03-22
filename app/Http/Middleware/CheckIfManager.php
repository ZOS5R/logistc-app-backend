<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class  CheckIfManager
{
    public function handle(Request $request, Closure $next)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is authenticated and if they are a manager (role_id = 1)
        if (!$user || $user->role_id !== 1) {
            return response()->json([
                'error' => 'Unauthorized, manager only',
            ], 403);
        }

        // Continue to the next request if the user is a manager
        return $next($request);
    }
}
