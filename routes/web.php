<?php

use App\Http\Controllers\Web\Admin\GroceryItemController as AdminWebGroceryController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\StoreController;
use Illuminate\Support\Facades\Route;

// Language Switcher (English / Bangla)
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/login/quick/{role}', [AuthController::class, 'quickLogin'])->name('login.quick');
});

// Authenticated Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public / Customer Storefront Views
Route::get('/', [StoreController::class, 'index'])->name('store.index');
Route::get('/orders', [StoreController::class, 'orders'])->name('store.orders')->middleware('auth');

// Admin Web Dashboard & Grocery Management Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/groceries', [AdminWebGroceryController::class, 'index'])->name('admin.groceries.index');
    Route::post('/groceries', [AdminWebGroceryController::class, 'store'])->name('admin.groceries.store');
    Route::put('/groceries/{id}', [AdminWebGroceryController::class, 'update'])->name('admin.groceries.update');
    Route::delete('/groceries/{id}', [AdminWebGroceryController::class, 'destroy'])->name('admin.groceries.destroy');
    Route::patch('/groceries/{id}/stock', [AdminWebGroceryController::class, 'updateStock'])->name('admin.groceries.stock');
});
