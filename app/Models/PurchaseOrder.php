<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'total_amount',
        'notes',
        'user_id'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::creating(function ($po) {
            $po->po_number = static::generatePoNumber();
        });
    }

    public static function generatePoNumber()
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $lastPo = static::where('po_number', 'like', $prefix . '%')->orderBy('po_number', 'desc')->first();

        if ($lastPo) {
            $lastNumber = (int) substr($lastPo->po_number, strlen($prefix));
            return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }

}
