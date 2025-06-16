<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Pivot;

class DiscountProduct extends Pivot
{
    public $incrementing = false;

    protected $fillable = [
        'discount_id',
        'product_id',
    ];

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function product()
    {
        return $this->belongs(Product::class);
    }
}
