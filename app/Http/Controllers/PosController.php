<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Discount;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use DB;

class PosController extends Controller
{
    public function posCategories()
    {
        return Category::all();
    }
    public function posProducts()
    {
        return Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->get();
    }

    public function validateDiscount(Request $request)
    {
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
    }

    public function posOrders(Request $request)
    {
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
    }
}
