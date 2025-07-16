<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Product;
use DateTime;
use Illuminate\Http\Request;
use App\Enums\DiscountType;
use Illuminate\Validation\Rules\Enum;
use function Illuminate\Support\enum_value;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $discounts = Discount::where('is_active', true)->paginate(50);
        return response()->json([
            'data' => $discounts
        ]);

    }
    // public function index()
    // {
    //     $discounts = Discount::where('is_active', true)
    //         ->where('start_date', '<=', now())
    //         ->where('end_date', '>=', now())
    //         ->get()
    //         ->map(function ($discount) {
    //             return [
    //                 'id' => $discount->id,
    //                 'code' => $discount->code,
    //                 'name' => $discount->name,
    //                 'type' => $discount->type,
    //                 'value' => $discount->value,
    //                 'scope' => $discount->scope,
    //                 'product_ids' => $discount->products->pluck('id')->toArray(),
    //                 // Add any other relevant fields
    //             ];
    //         });

    //     return response()->json($discounts);
    // }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:discounts,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'scope' => 'required|in:product,general',
            'is_active' => 'boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'apply_to_all_products' => 'nullable|boolean'
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['usage_limit'] = $validated['usage_limit'] ?? null;
        $validated['min_quantity'] = $validated['min_quantity'] ?? 1;
        $validated['min_amount'] = $validated['min_amount'] ?? 0;
        $validated['apply_to_all_products'] = $validated['apply_to_all_products'] ?? false;

        $discount = Discount::create($validated);

        // Attach specific products if this is a product discount
        if ($validated['scope'] === 'product' && !empty($validated['product_ids'])) {
            $discount->products()->sync($validated['product_ids']);
        }

        // Load the first product if it's a product discount
        if ($discount->scope === 'product' && $discount->products->count() > 0) {
            $discount->product_id = $discount->products->first()->id;
        }

        return response()->json([
            'success' => true,
            'message' => 'Discount created successfully',
            'discount' => $discount->load('products')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount)
    {
        $discount->load('products');

        return response()->json([
            'discount' => $discount,
            'products' => $discount->products
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:discounts,code,' . $discount->id,
            'type' => 'required|in:percentage,fixed,buy_x_get_y',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'scope' => 'required|in:product,general',
            'min_quantity' => 'nullable|integer|min:1',
            'min_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'apply_to_all_products' => 'boolean',
            'is_active' => 'boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id'
        ]);


        $discount->update($validated);

        // Handle product associations for product-scoped discounts
        if ($validated['scope'] === 'product') {
            if ($validated['apply_to_all_products'] ?? false) {
                $discount->products()->detach(); // Clear specific products if now applying to all
            } elseif (!empty($validated['product_ids'])) {
                $discount->products()->sync($validated['product_ids']);
            }
        } else {
            // Clear product associations if changing to general discount
            $discount->products()->detach();
        }

        return response()->json($discount);
    }

    public function eligibleProducts(Discount $discount)
    {
        if ($discount->scope === 'product' && !$discount->apply_to_all_products) {
            $products = $discount->products()->get();
        } else {
            // For general discounts or product discounts that apply to all
            $products = Product::all();
        }

        return response()->json($products);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount)
    {
        try {
            $discount->delete();
            return response()->json(['message' => 'Discount deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete discount'], 500);
        }
    }
}
