<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_sku',
        'quantity',
        'unit_cost',
        'total_cost'
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            // Automatically populate SKU when creating
            if (empty($item->product_sku) && $item->product) {
                $item->product_sku = $item->product->sku;
            }
        });
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
