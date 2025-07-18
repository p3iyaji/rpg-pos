<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'terms',
        'is_active',

    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('supplier_product_code', 'cost_price')
            ->withTimestamps();
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

}
