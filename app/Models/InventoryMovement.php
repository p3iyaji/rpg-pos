<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id',
        'product_sku',
        'quantity',
        'movement_type',
        'reference_id',
        'reference_type',
        'notes',
        'user_id'
    ];

    protected static function booted()
    {
        static::creating(function ($movement) {
            // Automatically populate SKU when creating
            if (empty($movement->product_sku) && $movement->product) {
                $movement->product_sku = $movement->product->sku;
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
