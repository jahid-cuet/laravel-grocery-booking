<?php

use App\Models\GroceryItem;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $adminRole = Role::where('slug', Role::ADMIN)->first();
    $this->adminUser = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $adminRole->id,
    ]);
    $this->adminToken = JWTAuth::fromUser($this->adminUser);

    $userRole = Role::where('slug', Role::USER)->first();
    $this->regularUser = User::create([
        'name' => 'Regular User',
        'email' => 'user@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);
    $this->userToken = JWTAuth::fromUser($this->regularUser);
});

test('admin can list all grocery items with pagination and filters', function () {
    GroceryItem::factory()->create([
        'name' => 'Organic Fresh Apples',
        'is_active' => true,
        'stock_quantity' => 20,
    ]);

    GroceryItem::factory()->create([
        'name' => 'Whole Wheat Bread',
        'is_active' => false,
        'stock_quantity' => 0,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
        ->getJson('/api/admin/groceries');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'formatted_price',
                    'stock_quantity',
                    'is_active',
                    'in_stock',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ])
        ->assertJsonCount(2, 'data');

    // Test search filter
    $searchResponse = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
        ->getJson('/api/admin/groceries?search=Apples');

    $searchResponse->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Organic Fresh Apples');
});

test('admin can add a new grocery item', function () {
    $payload = [
        'name' => 'Organic Honey 500g',
        'description' => 'Pure natural honey from wild beehives.',
        'price' => 8.50,
        'stock_quantity' => 30,
        'is_active' => true,
    ];

    $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
        ->postJson('/api/admin/groceries', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Organic Honey 500g')
        ->assertJsonPath('data.price', 8.5)
        ->assertJsonPath('data.stock_quantity', 30)
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('grocery_items', [
        'name' => 'Organic Honey 500g',
        'stock_quantity' => 30,
    ]);
});

test('admin cannot add grocery item with duplicate name or invalid data', function () {
    GroceryItem::factory()->create(['name' => 'Existing Milk']);

    $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
        ->postJson('/api/admin/groceries', [
            'name' => 'Existing Milk',
            'price' => -5,
            'stock_quantity' => -10,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'price', 'stock_quantity']);
});

test('admin can view single grocery item details', function () {
    $item = GroceryItem::factory()->create([
        'name' => 'Almond Milk 1L',
        'price' => 4.25,
        'stock_quantity' => 15,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
        ->getJson("/api/admin/groceries/{$item->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $item->id)
        ->assertJsonPath('data.name', 'Almond Milk 1L')
        ->assertJsonPath('data.price', 4.25);
});

test('admin can update grocery item details', function () {
    $item = GroceryItem::factory()->create([
        'name' => 'Old Item Name',
        'price' => 5.00,
        'stock_quantity' => 10,
    ]);

    $updatePayload = [
        'name' => 'Updated Item Name',
        'price' => 6.50,
        'stock_quantity' => 25,
        'description' => 'Updated item description.',
    ];

    $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
        ->putJson("/api/admin/groceries/{$item->id}", $updatePayload);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Item Name')
        ->assertJsonPath('data.price', 6.50)
        ->assertJsonPath('data.stock_quantity', 25);

    $this->assertDatabaseHas('grocery_items', [
        'id' => $item->id,
        'name' => 'Updated Item Name',
    ]);
});

test('admin can remove grocery item', function () {
    $item = GroceryItem::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
        ->deleteJson("/api/admin/groceries/{$item->id}");

    $response->assertOk()
        ->assertJson(['message' => 'Grocery item removed successfully.']);

    $this->assertSoftDeleted('grocery_items', [
        'id' => $item->id,
    ]);
});

test('admin can manage inventory and stock levels', function () {
    $item = GroceryItem::factory()->create([
        'stock_quantity' => 10,
    ]);

    // Test Set operation
    $responseSet = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
        ->patchJson("/api/admin/groceries/{$item->id}/inventory", [
            'quantity' => 50,
            'operation' => 'set',
        ]);

    $responseSet->assertOk()
        ->assertJsonPath('data.stock_quantity', 50);

    // Test Increment operation
    $responseInc = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
        ->patchJson("/api/admin/groceries/{$item->id}/inventory", [
            'quantity' => 20,
            'operation' => 'increment',
        ]);

    $responseInc->assertOk()
        ->assertJsonPath('data.stock_quantity', 70);

    // Test Decrement operation
    $responseDec = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
        ->patchJson("/api/admin/groceries/{$item->id}/inventory", [
            'quantity' => 30,
            'operation' => 'decrement',
        ]);

    $responseDec->assertOk()
        ->assertJsonPath('data.stock_quantity', 40);
});

test('regular user cannot access admin grocery routes', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->getJson('/api/admin/groceries');

    $response->assertForbidden();

    $postResponse = $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->postJson('/api/admin/groceries', [
            'name' => 'Hacker Item',
            'price' => 10,
            'stock_quantity' => 5,
        ]);

    $postResponse->assertForbidden();
});

test('unauthenticated guest cannot access admin grocery routes', function () {
    $this->getJson('/api/admin/groceries')
        ->assertUnauthorized();

    $this->postJson('/api/admin/groceries', ['name' => 'Guest Item'])
        ->assertUnauthorized();
});
