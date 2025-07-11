<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';
    protected $fillable = [
        'name',
        'slug',
        'barcode',
        'description',
        'image',
        'unit_id',
        'category_id',
        'price',
        'cost_price',
        'quantity',
        'is_active',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Automatically generate SKU if not provided.
     */
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($product) {
    //         if (empty($product->sku)) {
    //             $product->sku = Str::upper(Str::random(8)); // e.g., "XK92LM7Y"
    //             $product->slug = Str::slug($product->name);
    //         }
    //     });

    //     static::updating(function ($product) {
    //         $product->slug = Str::slug($product->name);
    //     });
    // }

    /**
     * Get the brand associated with the product.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if product is in low stock.
     */
    // public function isLowStock()
    // {
    //     return $this->quantity <= $this->low_stock_threshold;
    // }

    // public function categories()
    // {
    //     return $this->belongsToMany(Category::class)
    //         ->using(CategoryProduct::class);
    // }

    // public function discounts()
    // {
    //     return $this->belongsToMany(Discount::class)
    //         ->using(DiscountProduct::class);
    // }

}
