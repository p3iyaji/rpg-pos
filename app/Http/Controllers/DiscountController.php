<?php

namespace App\Http\Controllers;

use App\Models\Discount;
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
        return Discount::paginate(10);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:discounts,code',
            'type' => 'required|in:percentage,fixed,buy_x_get_y',
            'value' => 'required|numeric|min:0',
            //'start_date' => 'required|date|after_or_equal:today',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'min_quantity' => 'nullable|integer|min:1',
            'min_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $discount = Discount::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'min_quantity' => $validated['min_quantity'] ?? null,
            'min_amount' => $validated['min_amount'] ?? null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Discount created successfully',
            'data' => $discount
        ], 201);


    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount)
    {
        return response()->json($discount);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'name' => $request['name'],
            'code' => $request['code'],
            'type' => $request['type'],
            'value' => $request['value'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'min_quantity' => $request['min_quantity'],
            'min_amount' => $request['min_amount'],
        ]);

        $discount->update($validated);

        return response()->json($discount);
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
