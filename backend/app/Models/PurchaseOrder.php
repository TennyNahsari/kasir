<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_no',
        'vendor_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'location_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'total',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
        'terms_and_conditions',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected $appends = ['po_number', 'total_amount'];

    // Accessors
    public function getPoNumberAttribute()
    {
        return $this->po_no;
    }

    public function getTotalAmountAttribute()
    {
        return $this->total;
    }

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class, 'po_id');
    }
}
