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
        'sku',
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


    public function discounts()
    {
        return $this->belongsToMany(Discount::class)
            ->using(DiscountProduct::class);
    }

    public function applicableDiscounts()
    {
        return $this->belongsToMany(Discount::class)
            ->where(function ($query) {
                $query->where('scope', 'product')
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->where('apply_to_all_products', true)
                            ->orWhereHas('products', function ($q) {
                                $q->where('products.id', $this->id);
                            });
                    })
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            });
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class)
            ->withPivot('supplier_product_code', 'cost_price')
            ->withTimestamps();
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // Helper method to get current stock level
    public function currentStock()
    {
        return $this->inventoryMovements()->sum('quantity');
    }

    // Update product quantity based on inventory movements
    public function updateQuantity()
    {
        $this->quantity = $this->currentStock();
        $this->save();
    }


}
