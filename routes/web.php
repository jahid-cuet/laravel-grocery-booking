<?php

use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\StoreController;
use Illuminate\Support\Facades\Route;

// Grocery Storefront & Booking Views
Route::get('/', [StoreController::class, 'index'])->name('store.index');
Route::get('/orders', [StoreController::class, 'orders'])->name('store.orders');

// Language Switcher (English / Bangla)
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
