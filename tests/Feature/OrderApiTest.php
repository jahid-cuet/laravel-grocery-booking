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

    $userRole = Role::where('slug', Role::USER)->first();
    $this->user = User::create([
        'name' => 'Regular User',
        'email' => 'user@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);
    $this->userToken = JWTAuth::fromUser($this->user);

    $this->anotherUser = User::create([
        'name' => 'Another User',
        'email' => 'another@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);
    $this->anotherToken = JWTAuth::fromUser($this->anotherUser);
});

test('authenticated user can place an order with multiple items', function () {
    $item1 = GroceryItem::factory()->create([
        'name' => 'Organic Fresh Apples',
        'price' => 3.99,
        'stock_quantity' => 20,
        'is_active' => true,
    ]);

    $item2 = GroceryItem::factory()->create([
        'name' => 'Whole Milk 1L',
        'price' => 2.50,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->postJson('/api/orders', [
            'items' => [
                ['grocery_item_id' => $item1->id, 'quantity' => 2],
                ['grocery_item_id' => $item2->id, 'quantity' => 3],
            ],
            'notes' => 'Please deliver before noon.',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.total_amount', fn ($val) => abs((float) $val - 15.48) < 0.01)
        ->assertJsonStructure([
            'data' => [
                'id',
                'order_number',
                'status',
                'total_amount',
                'formatted_total',
                'notes',
                'items_count',
                'placed_at',
                'items' => [
                    '*' => ['id', 'grocery_item_id', 'grocery_item_name', 'quantity', 'unit_price', 'subtotal'],
                ],
            ],
        ]);

    // Stock must be reduced atomically
    expect($item1->fresh()->stock_quantity)->toBe(18);
    expect($item2->fresh()->stock_quantity)->toBe(7);

    $this->assertDatabaseHas('orders', [
        'user_id' => $this->user->id,
        'status' => 'confirmed',
    ]);
});

test('order placement fails with insufficient stock', function () {
    $item = GroceryItem::factory()->create([
        'name' => 'Rare Spice',
        'price' => 5.00,
        'stock_quantity' => 2,
        'is_active' => true,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->postJson('/api/orders', [
            'items' => [
                ['grocery_item_id' => $item->id, 'quantity' => 10],
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items']);

    // Stock must remain unchanged after failed order
    expect($item->fresh()->stock_quantity)->toBe(2);
    $this->assertDatabaseCount('orders', 0);
});

test('order placement fails for inactive grocery items', function () {
    $item = GroceryItem::factory()->create([
        'is_active' => false,
        'stock_quantity' => 50,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->postJson('/api/orders', [
            'items' => [
                ['grocery_item_id' => $item->id, 'quantity' => 1],
            ],
        ]);

    $response->assertUnprocessable();
    $this->assertDatabaseCount('orders', 0);
});

test('order price is snapshotted at time of booking', function () {
    $item = GroceryItem::factory()->create([
        'price' => 10.00,
        'stock_quantity' => 20,
        'is_active' => true,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->postJson('/api/orders', [
            'items' => [
                ['grocery_item_id' => $item->id, 'quantity' => 1],
            ],
        ]);

    $response->assertCreated();

    // Change item price after order placed
    $item->update(['price' => 99.99]);

    // Order item must preserve original unit price 10.00
    $this->assertDatabaseHas('order_items', [
        'grocery_item_id' => $item->id,
        'unit_price' => 10.00,
    ]);
});

test('authenticated user can view their order history', function () {
    $item = GroceryItem::factory()->create([
        'price' => 5.00,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);

    $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->postJson('/api/orders', [
            'items' => [['grocery_item_id' => $item->id, 'quantity' => 1]],
        ]);

    $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->postJson('/api/orders', [
            'items' => [['grocery_item_id' => $item->id, 'quantity' => 2]],
        ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->getJson('/api/orders');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => ['*' => ['id', 'order_number', 'status', 'total_amount', 'items']],
            'links',
            'meta',
        ]);
});

test('user can view a single order by id', function () {
    $item = GroceryItem::factory()->create([
        'price' => 7.00,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    $createResponse = $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->postJson('/api/orders', [
            'items' => [['grocery_item_id' => $item->id, 'quantity' => 2]],
        ]);

    $orderId = $createResponse->json('data.id');

    $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->getJson("/api/orders/{$orderId}");

    $response->assertOk()
        ->assertJsonPath('data.id', $orderId)
        ->assertJsonPath('data.total_amount', fn ($val) => abs((float) $val - 14.00) < 0.01);
});

test('user cannot view another users order', function () {
    $item = GroceryItem::factory()->create([
        'price' => 5.00,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    $createResponse = $this->actingAs($this->user, 'api')
        ->postJson('/api/orders', [
            'items' => [['grocery_item_id' => $item->id, 'quantity' => 1]],
        ]);

    $orderId = $createResponse->json('data.id');

    // Another user tries to access this order
    $response = $this->actingAs($this->anotherUser, 'api')
        ->getJson("/api/orders/{$orderId}");

    $response->assertStatus(404);
});

test('unauthenticated user cannot place an order', function () {
    $item = GroceryItem::factory()->create(['stock_quantity' => 10, 'is_active' => true]);

    $this->postJson('/api/orders', [
        'items' => [['grocery_item_id' => $item->id, 'quantity' => 1]],
    ])->assertUnauthorized();
});

test('order with non-existent grocery item fails validation', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->userToken}")
        ->postJson('/api/orders', [
            'items' => [
                ['grocery_item_id' => 9999, 'quantity' => 1],
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items.0.grocery_item_id']);
});
