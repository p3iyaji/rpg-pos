<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
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
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'product_id' => 'nullable|exists:products,id'
        ]);

        $code = $request->input('code');
        $amount = $request->input('amount');
        $quantity = $request->input('quantity');
        $productId = $request->input('product_id');

        $discount = Discount::where('code', $code)
            ->with('products')
            ->first();

        if (!$discount) {
            return response()->json([
                'valid' => false,
                'message' => 'Discount code not found'
            ], 404);
        }

        $product = $productId ? Product::find($productId) : null;

        // Check if discount is product-specific but no product was specified
        if ($discount->scope === 'product' && !$product) {
            return response()->json([
                'valid' => false,
                'message' => 'This discount must be applied to a specific product'
            ]);
        }

        if (!$discount->isApplicable($amount, $quantity, $product)) {
            return response()->json([
                'valid' => false,
                'message' => 'Discount is not applicable'
            ]);
        }

        $discountAmount = $discount->calculateDiscount($amount);
        $applicableTo = $product ? ['product_id' => $product->id] : [];

        return response()->json([
            'valid' => true,
            'discount' => $discount,
            'discount_amount' => $discountAmount,
            'scope' => $discount->scope,
            'applicable_to' => $applicableTo
        ]);
    }

    public function productDiscounts($productId)
    {
        $product = Product::with(['applicableDiscounts'])->findOrFail($productId);

        return response()->json([
            'discounts' => $product->applicableDiscounts
        ]);
    }

    public function posOrders(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_id' => 'nullable|exists:discounts,id',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'product_discounts' => 'required|numeric|min:0',
            'general_discount' => 'required|numeric|min:0',
            'general_discount_id' => 'nullable|exists:discounts,id',
            'total_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'customer_id' => $request->input('customer_id'),
                'subtotal' => $validated['subtotal'],
                'product_discounts' => $validated['product_discounts'],
                'general_discount' => $validated['general_discount'],
                'general_discount_id' => $validated['general_discount_id'] ?? null,
                'payment_method' => $request->input('payment_method'),
                'amount_tendered' => $request->input('amount_tendered'),
                'change_due' => $request->input('change_due'),
                'total' => $validated['total_amount'],
                'status' => OrderStatus::COMPLETED,
            ]);

            foreach ($validated['items'] as $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'discount_id' => $item['discount_id'] ?? null,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total' => ($item['quantity'] * $item['price']) - ($item['discount_amount'] ?? 0)
                ]);

                // Decrement product quantity
                Product::where('id', $item['product_id'])
                    ->decrement('quantity', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order' => $order->load('items')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage()
            ], 500);
        }
    }
}
