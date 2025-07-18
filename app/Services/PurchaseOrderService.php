<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Services\InventoryService;

class PurchaseOrderService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function receiveOrder(PurchaseOrder $order)
    {
        if ($order->status !== 'ordered') {
            throw new \Exception('Only ordered purchase orders can be received');
        }

        \DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $this->inventoryService->recordMovement(
                    $item->product,
                    $item->quantity,
                    'purchase',
                    $order,
                    "Received from PO {$order->po_number}"
                );
            }

            $order->update(['status' => 'received']);
        });
    }
}
