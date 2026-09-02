<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Register a new user and generate a JWT token.
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string, token_type: string, expires_in: int}
     */
    public function register(array $data): array
    {
        $roleSlug = $data['role'] ?? Role::USER;
        $role = Role::where('slug', $roleSlug)->firstOrFail();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
        ]);

        $user->load('role');

        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) auth('api')->factory()->getTTL() * 60,
        ];
    }

    /**
     * Authenticate user credentials and issue a JWT token.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{user: User, token: string, token_type: string, expires_in: int}|null
     */
    public function login(array $credentials): ?array
    {
        if (! $token = auth('api')->attempt($credentials)) {
            return null;
        }

        /** @var User $user */
        $user = auth('api')->user();
        $user->load('role');

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) auth('api')->factory()->getTTL() * 60,
        ];
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(): void
    {
        auth('api')->logout();
    }

    /**
     * Refresh a token.
     *
     * @return array{token: string, token_type: string, expires_in: int}
     */
    public function refresh(): array
    {
        $newToken = auth('api')->refresh();

        return [
            'token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => (int) auth('api')->factory()->getTTL() * 60,
        ];
    }

    /**
     * Get the authenticated User with role loaded.
     */
    public function me(): ?User
    {
        /** @var User|null $user */
        $user = auth('api')->user();
        $user?->load('role');

        return $user;
    }
}
