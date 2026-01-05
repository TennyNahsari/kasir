<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_id',
        'po_item_id',
        'product_id',
        'quantity_ordered',
        'quantity_received',
        'quantity_rejected',
        'unit_price',
        'line_total',
        'quality_status',
        'quality_notes',
        'notes',
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:4',
        'quantity_received' => 'decimal:4',
        'quantity_rejected' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    // Relationships
    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'grn_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
