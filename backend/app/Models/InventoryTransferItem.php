<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'product_id',
        'quantity_requested',
        'quantity_received',
        'quantity_rejected',
        'notes',
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:4',
        'quantity_received' => 'decimal:4',
        'quantity_rejected' => 'decimal:4',
    ];

    // Relationships
    public function transfer()
    {
        return $this->belongsTo(InventoryTransfer::class, 'transfer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
