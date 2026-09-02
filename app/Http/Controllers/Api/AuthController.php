<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => [
                'user' => new UserResource($result['user']),
                'authorisation' => [
                    'token' => $result['token'],
                    'type' => $result['token_type'],
                    'expires_in' => $result['expires_in'],
                ],
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Authenticate and get token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (! $result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($result['user']),
                'authorisation' => [
                    'token' => $result['token'],
                    'type' => $result['token_type'],
                    'expires_in' => $result['expires_in'],
                ],
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ], Response::HTTP_OK);
    }

    /**
     * Refresh a token.
     */
    public function refresh(): JsonResponse
    {
        $result = $this->authService->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Token refreshed successfully',
            'data' => [
                'authorisation' => [
                    'token' => $result['token'],
                    'type' => $result['token_type'],
                    'expires_in' => $result['expires_in'],
                ],
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Get the authenticated User profile.
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'status' => 'success',
            'data' => new UserResource($user),
        ], Response::HTTP_OK);
    }
}
