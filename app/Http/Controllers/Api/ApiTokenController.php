<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ApiTokenController extends Controller
{
    /**
     * Display a listing of API tokens.
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = ApiToken::select(['id', 'name', 'abilities', 'expires_at', 'last_used_at', 'usage_count', 'is_active', 'description', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'tokens' => $tokens
        ]);
    }

    /**
     * Create a new API token.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string|in:read,write,delete,admin',
            'expires_at' => 'nullable|date|after:now',
            'description' => 'nullable|string|max:500'
        ]);

        $user = $request->user();
        
        // Check if user has permission to create tokens with requested abilities
        $requestedAbilities = $request->input('abilities', ['read']);
        
        if (in_array('admin', $requestedAbilities) && !$user->hasRole('superadmin')) {
            throw ValidationException::withMessages([
                'abilities' => ['You do not have permission to create admin tokens.'],
            ]);
        }

        $expiresAt = $request->input('expires_at') ? Carbon::parse($request->input('expires_at')) : null;

        $result = ApiToken::generateToken(
            user: null,
            name: $request->input('name'),
            abilities: $requestedAbilities,
            expiresAt: $expiresAt,
            description: $request->input('description')
        );

        return response()->json([
            'message' => 'API token created successfully',
            'token' => $result['token'],
            'plain_text_token' => $result['plain_text_token'],
            'warning' => 'Please save this token securely. It will not be shown again.'
        ], 201);
    }

    /**
     * Display the specified API token.
     */
    public function show(Request $request, ApiToken $apiToken): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is superadmin
        if (!$user->hasRole('superadmin')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You do not have permission to view this token.'
            ], 403);
        }

        return response()->json([
            'token' => $apiToken
        ]);
    }

    /**
     * Update the specified API token.
     */
    public function update(Request $request, ApiToken $apiToken): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is superadmin
        if (!$user->hasRole('superadmin')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You do not have permission to update this token.'
            ], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'abilities' => 'sometimes|array',
            'abilities.*' => 'string|in:read,write,delete,admin',
            'expires_at' => 'sometimes|nullable|date|after:now',
            'description' => 'sometimes|nullable|string|max:500',
            'is_active' => 'sometimes|boolean'
        ]);

        $updateData = $request->only(['name', 'abilities', 'expires_at', 'description', 'is_active']);
        
        // Check abilities permission
        if (isset($updateData['abilities']) && in_array('admin', $updateData['abilities']) && !$user->hasRole('admin')) {
            throw ValidationException::withMessages([
                'abilities' => ['You do not have permission to assign admin abilities.'],
            ]);
        }

        if (isset($updateData['expires_at'])) {
            $updateData['expires_at'] = $updateData['expires_at'] ? Carbon::parse($updateData['expires_at']) : null;
        }

        $apiToken->update($updateData);

        return response()->json([
            'message' => 'API token updated successfully',
            'token' => $apiToken->fresh()
        ]);
    }

    /**
     * Revoke the specified API token.
     */
    public function destroy(Request $request, ApiToken $apiToken): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is superadmin
        if (!$user->hasRole('superadmin')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You do not have permission to revoke this token.'
            ], 403);
        }

        $apiToken->revoke();

        return response()->json([
            'message' => 'API token revoked successfully'
        ]);
    }

    /**
     * Create a public API token (for admin use).
     */
    public function createPublicToken(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasRole('superadmin')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only superadministrators can create public API tokens.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string|in:read,write,delete',
            'expires_at' => 'nullable|date|after:now',
            'description' => 'nullable|string|max:500'
        ]);

        $expiresAt = $request->input('expires_at') ? Carbon::parse($request->input('expires_at')) : null;

        $result = ApiToken::generateToken(
            user: null, // Public token not tied to specific user
            name: $request->input('name'),
            abilities: $request->input('abilities', ['read']),
            expiresAt: $expiresAt,
            description: $request->input('description')
        );

        return response()->json([
            'message' => 'Public API token created successfully',
            'token' => $result['token'],
            'plain_text_token' => $result['plain_text_token'],
            'warning' => 'Please save this token securely. It will not be shown again.'
        ], 201);
    }

    /**
     * List all API tokens (admin only).
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasRole('superadmin')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only superadministrators can view all API tokens.'
            ], 403);
        }

        $tokens = ApiToken::select(['id', 'name', 'abilities', 'expires_at', 'last_used_at', 'usage_count', 'is_active', 'description', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'tokens' => $tokens
        ]);
    }
}
