<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    protected $poService;

    public function __construct(PurchaseOrderService $poService)
    {
        $this->poService = $poService;
    }

    public function index()
    {
        return PurchaseOrder::with(['supplier', 'items.product'])
            ->orderBy('order_date', 'desc')
            ->paginate(25);
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $validated = $request->validated();

        $po = \DB::transaction(function () use ($validated) {
            $po = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status' => 'draft',
                'total_amount' => 0, // Will be calculated
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id()
            ]);

            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_cost'];
                $totalAmount += $itemTotal;

                $po->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $itemTotal
                ]);
            }

            $po->update(['total_amount' => $totalAmount]);

            return $po;
        });

        return $po->load(['supplier', 'items.product']);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return $purchaseOrder->load(['supplier', 'items.product']);
    }

    public function updateStatus(PurchaseOrder $purchaseOrder, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,ordered,received,cancelled'
        ]);

        if ($validated['status'] === 'received') {
            $this->poService->receiveOrder($purchaseOrder);
        } else {
            $purchaseOrder->update(['status' => $validated['status']]);
        }

        return $purchaseOrder->fresh()->load(['supplier', 'items.product']);
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return response()->json(['message' => 'Only draft purchase orders can be deleted'], 422);
        }

        $purchaseOrder->delete();
        return response()->noContent();
    }

}
