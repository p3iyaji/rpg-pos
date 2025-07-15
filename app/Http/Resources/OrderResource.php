<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_no' => $this->order_number,
            'customer' => $this->whenLoaded('customer'),
            'user' => $this->whenLoaded('user', function () {
                return $this->user->name;
            }),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'subtotal' => $this->subtotal,
            'tax' => $this->tax_amount,
            'item_discounts' => $this->product_discounts,
            'general_discount' => $this->general_discount,
            'general_discount_id' => $this->general_discount_id,
            'amount_tendered' => $this->amount_tendered,
            'change_due' => $this->change_due,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'paymentMethod' => $this->paymentMethod,
            'total' => $this->total,
            'date' => $this->created_at,
        ];
    }
}
