<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_id',
        'product_id',
        'quantity',
        'quantity_ordered',
        'estimated_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'quantity_ordered' => 'decimal:4',
        'estimated_price' => 'decimal:2',
    ];

    protected $appends = ['quantity_outstanding'];

    // Computed
    public function getQuantityOutstandingAttribute()
    {
        return $this->quantity - $this->quantity_ordered;
    }

    // Relationships
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'pr_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'pr_item_id');
    }
}
