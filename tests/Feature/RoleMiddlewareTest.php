<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    Route::middleware(['auth:api', 'role:admin'])->get('/test-admin-only', function () {
        return response()->json(['message' => 'admin-ok']);
    });

    Route::middleware(['auth:api', 'role:user'])->get('/test-user-only', function () {
        return response()->json(['message' => 'user-ok']);
    });

    Route::middleware(['auth:api', 'role:admin,user'])->get('/test-admin-or-user', function () {
        return response()->json(['message' => 'shared-ok']);
    });
});

test('unauthenticated request to role-protected route returns 401', function () {
    $response = $this->getJson('/test-admin-only');

    $response->assertUnauthorized();
});

test('regular user is forbidden from admin route', function () {
    $userRole = Role::where('slug', Role::USER)->first();
    $user = User::create([
        'name' => 'Regular User',
        'email' => 'regular@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);

    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/test-admin-only');

    $response->assertForbidden()
        ->assertJson([
            'status' => 'error',
            'message' => 'Forbidden. You do not have permission to access this resource.',
            'required_roles' => ['admin'],
            'current_role' => 'user',
        ]);
});

test('admin user can access admin route', function () {
    $adminRole = Role::where('slug', Role::ADMIN)->first();
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $adminRole->id,
    ]);

    $token = JWTAuth::fromUser($admin);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/test-admin-only');

    $response->assertOk()
        ->assertJson(['message' => 'admin-ok']);
});

test('regular user can access user route', function () {
    $userRole = Role::where('slug', Role::USER)->first();
    $user = User::create([
        'name' => 'Regular User',
        'email' => 'regular@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);

    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/test-user-only');

    $response->assertOk()
        ->assertJson(['message' => 'user-ok']);
});

test('both admin and user can access shared multi-role route', function () {
    $adminRole = Role::where('slug', Role::ADMIN)->first();
    $userRole = Role::where('slug', Role::USER)->first();

    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $adminRole->id,
    ]);

    $user = User::create([
        'name' => 'Regular User',
        'email' => 'regular@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);

    $adminToken = JWTAuth::fromUser($admin);
    $userToken = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$adminToken}")
        ->getJson('/test-admin-or-user')
        ->assertOk()
        ->assertJson(['message' => 'shared-ok']);

    $this->withHeader('Authorization', "Bearer {$userToken}")
        ->getJson('/test-admin-or-user')
        ->assertOk()
        ->assertJson(['message' => 'shared-ok']);
});
