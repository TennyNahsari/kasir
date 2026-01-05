<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'address',
        'phone',
        'person_in_charge',
        'is_active',
        'outlet_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function inventoryLedgers()
    {
        return $this->hasMany(InventoryLedger::class);
    }

    public function transfersFrom()
    {
        return $this->hasMany(InventoryTransfer::class, 'from_location_id');
    }

    public function transfersTo()
    {
        return $this->hasMany(InventoryTransfer::class, 'to_location_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
