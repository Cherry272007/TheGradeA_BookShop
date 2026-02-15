<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/books/{id}',[BookController::class,'show'])->name('books.single_show');
Route::get('/books',[BookController::class,'index'])->name('books.index');
/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Sanctum Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Authentication ---
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- User Self-Management ---
    Route::prefix('me')->group(function () {
        Route::get('/', [UserController::class, 'profile'])->name('user.profile');
        Route::post('/update', [UserController::class, 'updateProfile'])->name('user.update_profile');
        Route::post('/change-password', [UserController::class, 'changePassword'])->name('user.change_password');
        Route::delete('/delete-account', [UserController::class, 'selfDelete'])->name('user.self_delete'); 
        
        // 🛒 CUSTOMER ORDER ROUTES
        Route::get('/orders', [OrderController::class, 'index'])->name('user.orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('user.orders.store');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('user.orders.show');
    });

    // --- Admin Only Section ---
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        
        // Admin CRUD for Users
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index'); 
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');         
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('admin.users.restore');   
        Route::delete('/users/{id}/force', [UserController::class, 'permanentDelete'])->name('admin.users.force_delete'); 

        // Admin CRUD for Categories
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('categories.show');
        Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy'); 
        Route::post('/categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore'); 
        Route::delete('/categories/{id}/force', [CategoryController::class, 'permanentDelete'])->name('categories.force_delete'); 

        //Admin CRUD for Books
        Route::post('/books',[BookController::class,'store'])->name('books.store');
        Route::post('/books/{id}', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('books.soft_delete'); 
        Route::post('/books/{id}/restore', [BookController::class, 'restore'])->name('books.restore');
        Route::delete('/books/{id}/force', [BookController::class, 'permanentDelete'])->name('books.force_delete');
        Route::patch('/books/{id}/stock', [BookController::class, 'restock'])->name('books.update_stock');

        // Admin Order Management
        Route::get('/orders', [OrderController::class, 'adminIndex'])->name('admin.orders.index');
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update_status');  
    });
});