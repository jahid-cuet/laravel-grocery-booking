<?php

use App\Models\GroceryItem;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('users can view list of available grocery items', function () {
    // 2 available items
    GroceryItem::factory()->create([
        'name' => 'Fresh Green Apples',
        'is_active' => true,
        'stock_quantity' => 15,
        'price' => 3.99,
    ]);

    GroceryItem::factory()->create([
        'name' => 'Organic Whole Milk',
        'is_active' => true,
        'stock_quantity' => 20,
        'price' => 4.50,
    ]);

    // Inactive item (should NOT be visible)
    GroceryItem::factory()->create([
        'name' => 'Inactive Item',
        'is_active' => false,
        'stock_quantity' => 50,
    ]);

    // Out of stock item (should NOT be visible)
    GroceryItem::factory()->create([
        'name' => 'Out of stock Item',
        'is_active' => true,
        'stock_quantity' => 0,
    ]);

    $response = $this->getJson('/api/groceries');

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
});

test('user can view specific grocery item details', function () {
    $item = GroceryItem::factory()->create([
        'name' => 'Farm Fresh Eggs',
        'price' => 4.99,
        'stock_quantity' => 30,
        'is_active' => true,
    ]);

    $response = $this->getJson("/api/groceries/{$item->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $item->id)
        ->assertJsonPath('data.name', 'Farm Fresh Eggs')
        ->assertJsonPath('data.price', 4.99);
});

test('viewing non-existent grocery item returns 404', function () {
    $response = $this->getJson('/api/groceries/99999');

    $response->assertNotFound();
});
