<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'category_id',
        'type',
        'item_type',
        'uom',
        'track_inventory',
        'cost_price',
        'selling_price',
        'stock',
        'min_stock',
        'min_stock_level',
        'max_stock_level',
        'reorder_level',
        'last_purchase_price',
        'average_cost',
        'track_stock',
        'image',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'last_purchase_price' => 'decimal:2',
        'average_cost' => 'decimal:2',
        'min_stock_level' => 'decimal:4',
        'max_stock_level' => 'decimal:4',
        'reorder_level' => 'decimal:4',
        'track_stock' => 'boolean',
        'track_inventory' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function inventoryLedgers()
    {
        return $this->hasMany(InventoryLedger::class);
    }

    public function purchaseRequestItems()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    // Helpers
    public function isLowStock(): bool
    {
        return $this->track_stock && $this->stock <= $this->min_stock;
    }

    public function decreaseStock(int $quantity): void
    {
        if ($this->track_stock) {
            $this->decrement('stock', $quantity);
        }
    }

    public function increaseStock(int $quantity): void
    {
        if ($this->track_stock) {
            $this->increment('stock', $quantity);
        }
    }
}
