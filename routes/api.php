<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CategoryController;
use App\Models\Product;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;


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

Route::get('/pos-products', function () {
    return Product::with(['category', 'unit'])
        ->where('is_active', true)
        ->where('quantity', '>', 0)
        ->get();
});

Route::get('/pos-categories', function () {
    return Category::all();
});

Route::get('/pos-discounts/validate', function (Request $request) {
    $code = $request->input('code');
    $amount = $request->input('amount', 0);
    $quantity = $request->input('quantity', 0);

    $discount = Discount::where('code', $code)->first();

    if (!$discount) {
        return response()->json([
            'valid' => false,
            'message' => 'Discount code not found'
        ]);
    }

    if (!$discount->isApplicable($amount, $quantity)) {
        return response()->json([
            'valid' => false,
            'message' => 'Discount is not applicable to this order'
        ]);
    }

    return response()->json([
        'valid' => true,
        'discount' => $discount
    ]);
});

Route::post('/pos-orders', function (Request $request) {
    $validated = $request->validate([
        'items' => 'required|array',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'discount_id' => 'nullable|exists:discounts,id',
        'total_amount' => 'required|numeric|min:0'
    ]);

    DB::beginTransaction();

    try {
        // Create the order
        $order = Order::create([
            'user_id' => auth()->id(),
            'discount_id' => $validated['discount_id'] ?? null,
            'total_amount' => $validated['total_amount'],
            'status' => 'completed'
        ]);

        // Add order items
        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['quantity'] * $item['price']
            ]);

            // Update product quantity
            Product::where('id', $item['product_id'])
                ->decrement('quantity', $item['quantity']);
        }

        // Record discount usage if applicable
        if ($validated['discount_id']) {
            Discount::where('id', $validated['discount_id'])
                ->increment('usage_count');
        }

        DB::commit();

        return response()->json($order->load('items'));
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Error creating order: ' . $e->getMessage()
        ], 500);
    }
});