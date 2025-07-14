<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name' => $this->whenLoaded('product', function () {
                return $this->product->name;
            }),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount' => $this->discount_amount,
            'tax' => $this->tax_amount,
            'total' => $this->total_price,
            'date' => $this->created_at,
        ];
    }
}
