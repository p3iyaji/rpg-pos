<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Support\Str;
use Storage;
use Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::with('unit', 'category')->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validatedData = $request->validated();
        $data = $validatedData;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $data['image'] = $file->store('product_images', 'public');
        }

        $data['slug'] = Str::slug($validatedData['name']);
        $data['user_id'] = Auth::id();

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product added successfully',
            'product' => $product,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {


        if ($request->hasFile('image')) {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'barcode' => 'nullable|string',
                'description' => 'nullable|string',
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2053',
                'unit_id' => 'required',
                'category_id' => 'required',
                'price' => 'required',
                'cost_price' => 'required',
                'quantity' => 'required|integer',
                'is_active' => 'required|boolean'
            ]);

            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $file = $request->file('image');
            $validatedData['image'] = $file->store('product_images', 'public');
        } else {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'barcode' => 'nullable|string',
                'description' => 'nullable|string',
                'unit_id' => 'required',
                'category_id' => 'required',
                'price' => 'required',
                'cost_price' => 'required',
                'quantity' => 'required|integer',
                'is_active' => 'required|boolean'
            ]);

        }

        $product->slug = Str::slug($validatedData['name']);
        $product->user_id = Auth::id();

        $product->fill($validatedData);
        $product->save();

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return response()->json(['message' => 'Product deleted successfully']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete product']);
        }
    }
}
