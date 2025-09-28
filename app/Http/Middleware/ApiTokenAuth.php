<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ApiToken;
use Carbon\Carbon;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check for API token in header
        $token = $request->header('X-API-Token') ?? $request->header('Authorization');
        
        // Remove 'Bearer ' prefix if present
        if ($token && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        if (!$token) {
            return response()->json([
                'error' => 'API token required',
                'message' => 'Please provide a valid API token in X-API-Token header or Authorization header'
            ], 401);
        }
        
        $apiToken = ApiToken::where('token', $token)
            ->where('is_active', true)
            ->first();

        if (!$apiToken) {
            return response()->json([
                'error' => 'Invalid API token',
                'message' => 'The provided API token is invalid or has been revoked'
            ], 401);
        }

        // Check if token has expired
        if ($apiToken->expires_at && $apiToken->expires_at->isPast()) {
            return response()->json([
                'error' => 'API token expired',
                'message' => 'The provided API token has expired'
            ], 401);
        }

        // Update last used timestamp
        $apiToken->update([
            'last_used_at' => Carbon::now(),
            'usage_count' => $apiToken->usage_count + 1
        ]);

        // Add token info to request for potential use in controllers
        $request->merge(['api_token' => $apiToken]);

        return $next($request);
    }
}
