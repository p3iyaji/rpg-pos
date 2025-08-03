<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\SkuService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Support\Str;

use Storage;
use Auth;
use Log;
use DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::with('unit', 'category')->OrderBy('name', 'ASC')->paginate(100);
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

        // Generate SKU
        $category = Category::find($validatedData['category_id']);

        if (!$category) {
            throw new \Exception("Category not found");
        }

        $nextId = Product::withTrashed()->max('id') ?? 0;
        $nextId++;

        $skuService = app(SkuService::class);
        $data['sku'] = $skuService->generateForCategory($category, $nextId);

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product added successfully',
            'product' => $product,
        ], 201);
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
                'barcode' => 'required|string',
                'description' => 'nullable|string',
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2053',
                'unit_id' => 'required',
                'category_id' => 'required',
                'price' => 'required|decimal:0,2|min:0',
                'cost_price' => 'required|decimal:0,2|min:0',
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
                'price' => 'required|decimal:0,2|min:0',
                'cost_price' => 'required|decimal:0,2|min:0',
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


    public function topSelling(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:10',
            'days' => 'sometimes|integer|min:1'
        ]);

        $limit = $validated['limit'] ?? 1;
        $days = $validated['days'] ?? 30;

        $products = Product::query()
            ->select([
                'products.*',
                'sales.total_quantity',
                'sales.revenue'
            ])
            ->joinSub(function ($query) use ($days) {
                $query->from('order_items')
                    ->select([
                        'product_id',
                        DB::raw('SUM(quantity) as total_quantity'),
                        DB::raw('SUM(total_price) as revenue')
                    ])
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.created_at', '>=', now()->subDays($days))
                    ->groupBy('product_id');
            }, 'sales', function ($join) {
                $join->on('products.id', '=', 'sales.product_id');
            })
            ->orderByDesc('sales.revenue')
            ->limit($limit)
            ->get();
        // Return consistent response structure
        return response()->json([
            'success' => true,
            'data' => $limit === 1 ? ($products[0] ?? null) : $products
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
