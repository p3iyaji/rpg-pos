<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ProfitAndLossController;



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
Route::post('/pos-discounts', [DiscountController::class, 'store'])->name('pos-discounts');

Route::apiResource('customers', CustomerController::class)->middleware('auth:sanctum');
//pos routes
Route::get('/pos-products', [PosController::class, 'posProducts'])->middleware('auth:sanctum');
Route::get('/pos-categories', [PosController::class, 'posCategories'])->middleware('auth:sanctum');
Route::get('/pos-discounts/validate', [PosController::class, 'validateDiscount'])->middleware('auth:sanctum');
Route::post('/pos-orders', [PosController::class, 'posOrders'])->middleware('auth:sanctum');
Route::get('/api/pos-products/{product}/discounts', [PosController::class, 'productDiscounts']);

//orders 
Route::apiResource('orders', OrderController::class)->middleware('auth:sanctum');

Route::post('/pos-orders/draft', [PosController::class, 'saveDraft']);
Route::get('/pos-orders/drafts', [PosController::class, 'getDrafts']);
Route::post('/pos-orders/refund', [OrderController::class, 'processRefund']);
Route::post('/pos-orders/search', [OrderController::class, 'searchOrders']);

//suppliers
Route::apiResource('suppliers', SupplierController::class)->middleware('auth:sanctum');
Route::apiResource('purchase-orders', PurchaseOrderController::class)->middleware('auth:sanctum');
Route::apiResource('expenses', ExpenseController::class)->middleware('auth:sanctum');
Route::apiResource('expense-categories', ExpenseCategoryController::class)->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // ... other routes
    Route::get('reports/profit-and-loss', [ProfitAndLossController::class, 'index']);
    Route::get('reports/profit-summary', [ProfitAndLossController::class, 'summary']);
    Route::apiResource('expenses', ExpenseController::class);
    Route::get('orders/summary', [OrderController::class, 'summary']);
    Route::get('products/top-selling', [ProductController::class, 'topSelling']);
});

