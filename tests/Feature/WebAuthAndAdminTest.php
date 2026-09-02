<?php

use App\Models\GroceryItem;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $adminRole = Role::where('slug', Role::ADMIN)->first();
    $this->admin = User::create([
        'name' => 'System Administrator',
        'email' => 'admin@grocery.com',
        'password' => Hash::make('password123'),
        'role_id' => $adminRole->id,
    ]);

    $userRole = Role::where('slug', Role::USER)->first();
    $this->customer = User::create([
        'name' => 'Store Customer',
        'email' => 'user@grocery.com',
        'password' => Hash::make('password123'),
        'role_id' => $userRole->id,
    ]);
});

test('login page loads successfully with quick login options', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertSee('Sign in to your account')
        ->assertSee('Admin Portal')
        ->assertSee('Customer Store');
});

test('guest can view the customer registration form', function () {
    $this->get('/register')
        ->assertOk()
        ->assertSee('Create your account')
        ->assertSee('Create an account');
});

test('customer can register from the web form', function () {
    $response = $this->post('/register', [
        'name' => 'New Web Customer',
        'email' => 'web-customer@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertRedirect(route('store.index'));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'web-customer@example.com',
        'role_id' => Role::where('slug', Role::USER)->value('id'),
    ]);
});

test('web registration validates password confirmation', function () {
    $response = $this->post('/register', [
        'name' => 'Attempted Admin',
        'email' => 'attempted-admin@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'different123',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('admin can login via web form and is redirected to admin dashboard', function () {
    $response = $this->post('/login', [
        'email' => 'admin@grocery.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('admin.groceries.index'));
    $this->assertAuthenticatedAs($this->admin);
});

test('customer can login via web form and is redirected to store', function () {
    $response = $this->post('/login', [
        'email' => 'user@grocery.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('store.index'));
    $this->assertAuthenticatedAs($this->customer);
});

test('evaluators can use 1-click quick login for admin and customer', function () {
    // Quick admin login
    $resAdmin = $this->get('/login/quick/admin');
    $resAdmin->assertRedirect(route('admin.groceries.index'));
    $this->assertAuthenticatedAs($this->admin);

    // Logout
    $this->post('/logout');

    // Quick customer login
    $resCustomer = $this->get('/login/quick/user');
    $resCustomer->assertRedirect(route('store.index'));
    $this->assertAuthenticatedAs($this->customer);
});

test('guest or regular customer cannot access admin dashboard', function () {
    // Guest gets redirected to login
    $this->get('/admin/groceries')
        ->assertRedirect(route('login'));

    // Customer gets 403 Forbidden
    $this->actingAs($this->customer)
        ->get('/admin/groceries')
        ->assertForbidden();
});

test('admin can access dashboard and create grocery item via web form', function () {
    $response = $this->actingAs($this->admin)
        ->get('/admin/groceries');

    $response->assertOk()
        ->assertSee('Admin Inventory Management')
        ->assertSee('Add Grocery Item');

    // Create item
    $postResponse = $this->actingAs($this->admin)
        ->post('/admin/groceries', [
            'name' => 'Fresh Dragonfruit 1kg',
            'description' => 'Exotic sweet organic dragonfruit.',
            'price' => 9.99,
            'stock_quantity' => 30,
            'is_active' => true,
        ]);

    $postResponse->assertRedirect(route('admin.groceries.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('grocery_items', [
        'name' => 'Fresh Dragonfruit 1kg',
        'stock_quantity' => 30,
    ]);
});

test('admin can update grocery item and manage stock via web forms', function () {
    $item = GroceryItem::factory()->create([
        'name' => 'Fresh Pineapples',
        'price' => 3.50,
        'stock_quantity' => 15,
    ]);

    // Update item
    $updateResponse = $this->actingAs($this->admin)
        ->put("/admin/groceries/{$item->id}", [
            'name' => 'Golden Sweet Pineapples',
            'price' => 4.20,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);

    $updateResponse->assertRedirect(route('admin.groceries.index'));
    expect($item->fresh()->name)->toBe('Golden Sweet Pineapples');

    // Adjust stock (increment)
    $stockResponse = $this->actingAs($this->admin)
        ->patch("/admin/groceries/{$item->id}/stock", [
            'quantity' => 10,
            'operation' => 'increment',
        ]);

    $stockResponse->assertRedirect(route('admin.groceries.index'));
    expect($item->fresh()->stock_quantity)->toBe(30);

    // Delete item
    $deleteResponse = $this->actingAs($this->admin)
        ->delete("/admin/groceries/{$item->id}");

    $deleteResponse->assertRedirect(route('admin.groceries.index'));
    $this->assertSoftDeleted('grocery_items', ['id' => $item->id]);
});
