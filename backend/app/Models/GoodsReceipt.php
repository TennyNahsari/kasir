<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_no',
        'po_id',
        'location_id',
        'receipt_date',
        'supplier_invoice_no',
        'supplier_invoice_date',
        'status',
        'is_posted',
        'posted_at',
        'posted_by',
        'quality_checked_by',
        'quality_checked_at',
        'quality_notes',
        'received_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'supplier_invoice_date' => 'date',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
        'quality_checked_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected $appends = ['grn_number', 'received_by_name', 'inspected_by_name', 'delivery_note_no', 'po_number', 'vendor_name'];

    // Accessors
    public function getGrnNumberAttribute()
    {
        return $this->grn_no;
    }

    public function getReceivedByNameAttribute()
    {
        return $this->receivedBy ? $this->receivedBy->name : null;
    }

    public function getInspectedByNameAttribute()
    {
        return $this->qualityCheckedBy ? $this->qualityCheckedBy->name : null;
    }

    public function getDeliveryNoteNoAttribute()
    {
        return $this->supplier_invoice_no;
    }

    public function getPoNumberAttribute()
    {
        return $this->purchaseOrder ? $this->purchaseOrder->po_number : null;
    }

    public function getVendorNameAttribute()
    {
        return $this->purchaseOrder && $this->purchaseOrder->vendor 
            ? $this->purchaseOrder->vendor->name 
            : null;
    }

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class, 'grn_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function qualityCheckedBy()
    {
        return $this->belongsTo(User::class, 'quality_checked_by');
    }
}
