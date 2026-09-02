<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('user can register successfully and receives JWT token', function () {
    $payload = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ];

    $response = $this->postJson('/api/auth/register', $payload);

    $response->assertCreated()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'role' => ['id', 'name', 'slug']],
                'authorisation' => ['token', 'type', 'expires_in'],
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'data' => [
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'role' => [
                        'slug' => 'user',
                    ],
                ],
                'authorisation' => [
                    'type' => 'bearer',
                ],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
    ]);
});

test('registration validates required fields and unique email', function () {
    $userRole = Role::where('slug', Role::USER)->first();
    User::create([
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'password' => Hash::make('password'),
        'role_id' => $userRole->id,
    ]);

    $response = $this->postJson('/api/auth/register', [
        'name' => '',
        'email' => 'existing@example.com',
        'password' => 'short',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('public registration cannot assign an admin role', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Untrusted Admin',
        'email' => 'untrusted-admin@example.com',
        'password' => 'secret123',
        'role' => 'admin',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['role']);

    $this->assertDatabaseMissing('users', [
        'email' => 'untrusted-admin@example.com',
    ]);
});

test('user can login with valid credentials', function () {
    $userRole = Role::where('slug', Role::USER)->first();
    User::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'role'],
                'authorisation' => ['token', 'type', 'expires_in'],
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'data' => [
                'user' => [
                    'email' => 'jane@example.com',
                ],
            ],
        ]);
});

test('user cannot login with invalid password', function () {
    $userRole = Role::where('slug', Role::USER)->first();
    User::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJson([
            'status' => 'error',
            'message' => 'Invalid email or password',
        ]);
});

test('authenticated user can view their profile via me endpoint', function () {
    $userRole = Role::where('slug', Role::USER)->first();
    $user = User::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);

    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/auth/me');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'email' => 'jane@example.com',
                'role' => [
                    'slug' => 'user',
                ],
            ],
        ]);
});

test('authenticated user can refresh their token', function () {
    $userRole = Role::where('slug', Role::USER)->first();
    $user = User::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);

    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/auth/refresh');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'authorisation' => ['token', 'type', 'expires_in'],
            ],
        ]);
});

test('authenticated user can logout and token is invalidated', function () {
    $userRole = Role::where('slug', Role::USER)->first();
    $user = User::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);

    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/auth/logout');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
});
