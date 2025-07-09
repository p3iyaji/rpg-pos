<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CategoryController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/hello', function () {
    return ['message' => 'Hello from Paul @ Realpay Global!'];
})->middleware('auth:sanctum');

// Authentication
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');

Route::apiResource('units', UnitController::class)->middleware('auth:sanctum');
Route::apiResource('categories', CategoryController::class)->middleware('auth:sanctum');
Route::apiResource('products', ProductController::class)->middleware('auth:sanctum');
Route::apiResource('discounts', DiscountController::class)->middleware('auth:sanctum');
Route::apiResource('customers', CustomerController::class)->middleware('auth:sanctum');
//pos routes
Route::get('/pos-products', [PosController::class, 'posProducts'])->middleware('auth:sanctum');
Route::get('/pos-categories', [PosController::class, 'posCategories'])->middleware('auth:sanctum');
Route::get('/pos-discounts/validate', [PosController::class, 'validateDiscount'])->middleware('auth:sanctum');
Route::post('/pos-orders', [PosController::class, 'posOrders'])->middleware('auth:sanctum');