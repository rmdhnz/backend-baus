<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil API key dari .env
        $validKey = config('services.internal_api.key');

        // Ambil dari header (x-api-key)
        $providedKey = $request->header('x-api-key');

        // Jika tidak cocok, tolak akses
        if (!$providedKey || $providedKey !== $validKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized – Invalid API Key',
            ], 401);
        }

        return $next($request);
    }
}
