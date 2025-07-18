<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Product;

class SupplierController extends Controller
{
    public function index()
    {
        return Supplier::withCount('products')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'terms' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        return Supplier::create($validated);
    }

    public function show(Supplier $supplier)
    {
        return $supplier->load('products');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'terms' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $supplier->update($validated);
        return $supplier;
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->products()->exists()) {
            return response()->json(['message' => 'Cannot delete supplier with associated products'], 422);
        }

        $supplier->delete();
        return response()->noContent();
    }

    public function attachProduct(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_product_code' => 'nullable|string|max:255',
            'cost_price' => 'required|numeric|min:0'
        ]);

        $product = Product::find($request->product_id);

        $supplier->products()->syncWithoutDetaching([
            $validated['product_id'] => [
                'supplier_product_code' => $validated['supplier_product_code'],
                'cost_price' => $validated['cost_price'],
                'product_sku' => $product->sku
            ]
        ]);

        return $supplier->load('products');
    }

    public function detachProduct(Supplier $supplier, $productId)
    {
        $supplier->products()->detach($productId);
        return $supplier->load('products');
    }

}
