<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Enums\OrderStatus;
use App\Models\Product;
use DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        ///$orders = Order::with('items.product', 'payments', 'user', 'customer')->paginate(10);

        $query = Order::with('items.product', 'payments', 'user', 'customer');

        // Add search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_number', 'like', "%{$searchTerm}%")
                    ->orWhere('status', 'like', "%{$searchTerm}%")
                    ->orWhere('total', 'like', "%{$searchTerm}%")
                    ->orWhereHas('customer', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('phone', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $orders = $query->paginate(10);

        $summary = [
            'total_item_discounts' => Order::sum('product_discounts'),
            'total_general_discount' => Order::sum('general_discount'),
            'total_sales' => Order::sum('total'),
            'total_orders' => Order::count(),
        ];

        return response()->json([
            'data' => OrderResource::collection($orders),
            'summary' => $summary,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
            'links' => [
                'first' => $orders->url(1),
                'last' => $orders->url($orders->lastPage()),
                'prev' => $orders->previousPageUrl(),
                'next' => $orders->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $order = Order::with(['items.product', 'customer', 'user'])
            ->findorFail($id);

        return response()->json([
            'success' => true,
            'data' => new OrderResource(($order))
        ]);
    }

    public function searchOrders(Request $request)
    {
        $query = $request->input('query');

        $orders = Order::with(['customer', 'items.product'])
            ->where(function ($q) use ($query) {
                $q->where('order_number', 'like', "%{$query}%")
                    ->orWhereHas('customer', function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    });
            })
            ->whereIn('status', ['completed', 'partially_refunded']) // Only refundable orders
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json($orders);
    }

    public function processRefund(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
            'payment_method' => 'required|in:cash,card,transfer',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Validate refund amount doesn't exceed remaining refundable amount
        $alreadyRefunded = $order->refunds()->sum('amount');
        $remainingRefundable = $order->total - $alreadyRefunded;

        if ($validated['amount'] > $remainingRefundable) {
            return response()->json([
                'success' => false,
                'message' => 'Refund amount cannot exceed remaining refundable amount of ' . number_format($remainingRefundable, 2),
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Create refund record
            $refund = $order->refunds()->create([
                'amount' => $validated['amount'],
                'reason' => $validated['reason'],
                'payment_method' => $validated['payment_method'],
                'processed_by' => auth()->id(),
            ]);

            // Re-stocking not activated yet as I don't think I want staff returning 
            // ...items to stock without proper check
            if ($request->has('restock_items') && $request->restock_items) {
                foreach ($order->items as $item) {
                    Product::where('id', $item->product_id)
                        ->increment('quantity', $item->quantity);
                }
            }

            // Update order financials
            $order->update([
                'product_discounts' => $order->product_discounts,
                'general_discount' => $order->general_discount,
                'total' => $order->total, // Keep original total
                'amount_refunded' => $order->refunds()->sum('amount'),
            ]);

            // Update order status
            $refundStatus = ($validated['amount'] == $remainingRefundable)
                ? OrderStatus::REFUNDED
                : OrderStatus::PARTIALLY_REFUNDED;

            $order->update(['status' => $refundStatus->value]);

            DB::commit();

            return response()->json([
                'success' => true,
                'refund' => $refund,
                'order' => new OrderResource($order->fresh()),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error processing refund: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
