<?php

use App\Models\GroceryItem;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('storefront homepage loads successfully and displays grocery catalogue', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('FreshCart Grocery')
        ->assertSee('Grocery Catalogue')
        ->assertSee('Organic Whole Milk 1L')
        ->assertDontSee('Fresh Baby Spinach 200g')
        ->assertDontSee('Seasonal Dragon Fruit');
});

test('orders page loads successfully', function () {
    $response = $this->actingAs(User::where('email', 'user@grocery.com')->first())->get('/orders');

    $response->assertOk()
        ->assertSee('My Orders');
});

test('orders page requires authentication and shows only the signed-in user orders', function () {
    $this->get('/orders')->assertRedirect(route('login'));

    $userRole = Role::where('slug', Role::USER)->first();
    $firstUser = User::where('email', 'user@grocery.com')->first();
    $secondUser = User::create([
        'name' => 'Second Customer',
        'email' => 'second@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);

    $item = GroceryItem::where('name', 'Organic Whole Milk 1L')->first();
    $firstUser->orders()->create([
        'order_number' => 'ORD-FIRST-123456',
        'total_amount' => 3.49,
        'status' => 'confirmed',
    ])->items()->create([
        'grocery_item_id' => $item->id,
        'quantity' => 1,
        'unit_price' => 3.49,
        'subtotal' => 3.49,
    ]);
    $secondUser->orders()->create([
        'order_number' => 'ORD-SECOND-123456',
        'total_amount' => 3.49,
        'status' => 'confirmed',
    ]);

    $this->actingAs($firstUser)->get('/orders')
        ->assertOk()
        ->assertSee('ORD-FIRST-123456')
        ->assertDontSee('ORD-SECOND-123456');
});

test('user can switch application locale to bangla and english', function () {
    // Switch to Bangla
    $responseBn = $this->get('/locale/bn');
    $responseBn->assertRedirect();
    $responseBn->assertSessionHas('locale', 'bn');

    // Access store with Bangla session
    $storeBn = $this->withSession(['locale' => 'bn'])->get('/');
    $storeBn->assertOk()
        ->assertSee('ফ্রেশকার্ট গ্রোসারি')
        ->assertSee('গ্রোসারি ক্যাটালগ');

    // Switch back to English
    $responseEn = $this->get('/locale/en');
    $responseEn->assertRedirect();
    $responseEn->assertSessionHas('locale', 'en');
});
