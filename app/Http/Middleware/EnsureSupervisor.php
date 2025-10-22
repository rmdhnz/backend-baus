<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSupervisor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next,...$roles): Response
    {
        $user = Auth::user();
        if (!$user) { 
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }
        if(!in_array($user->role_id, $roles)){
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Insufficient role.',
            ], 403);
        }

        if (!$user || $user->role_id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Supervisor only.',
            ], 403);
        }
        return $next($request);
    }
}
