<?php

use App\Http\Controllers\Api\Admin\GroceryItemController as AdminGroceryItemController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GroceryItemController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// User / Public Grocery Catalogue Routes
Route::get('/groceries', [GroceryItemController::class, 'index']);
Route::get('/groceries/{id}', [GroceryItemController::class, 'show']);

// Admin Protected Routes
Route::prefix('admin')->middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/groceries', [AdminGroceryItemController::class, 'index']);
    Route::post('/groceries', [AdminGroceryItemController::class, 'store']);
    Route::get('/groceries/{id}', [AdminGroceryItemController::class, 'show']);
    Route::put('/groceries/{id}', [AdminGroceryItemController::class, 'update']);
    Route::delete('/groceries/{id}', [AdminGroceryItemController::class, 'destroy']);
    Route::patch('/groceries/{id}/inventory', [AdminGroceryItemController::class, 'updateStock']);
});
