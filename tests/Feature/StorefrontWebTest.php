<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('storefront homepage loads successfully and displays grocery catalogue', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('FreshCart Grocery')
        ->assertSee('Grocery Catalogue')
        ->assertSee('Organic Whole Milk 1L');
});

test('orders page loads successfully', function () {
    $response = $this->get('/orders');

    $response->assertOk()
        ->assertSee('My Orders');
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
