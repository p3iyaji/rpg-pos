<?php
namespace App\Services;

use App\Models\Product;
use App\Models\InventoryMovement;

class InventoryService
{
    public function recordMovement(Product $product, $quantity, $movementType, $reference = null, $notes = null)
    {
        $movement = InventoryMovement::create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'movement_type' => $movementType,
            'reference_id' => $reference ? $reference->id : null,
            'reference_type' => $reference ? get_class($reference) : null,
            'notes' => $notes,
            'user_id' => auth()->id()
        ]);

        $product->updateQuantity();

        return $movement;
    }

    public function adjustStock(Product $product, $newQuantity, $reason = null)
    {
        $currentStock = $product->currentStock();
        $difference = $newQuantity - $currentStock;

        return $this->recordMovement(
            $product,
            $difference,
            'adjustment',
            null,
            $reason ?: 'Manual stock adjustment'
        );
    }
}
