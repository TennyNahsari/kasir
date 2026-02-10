<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'asset_tag',
        'serial_number',
        'location_id',
        'pic',
        'assigned_date',
        'status',
        'condition',
        'purchase_date',
        'purchase_price',
        'useful_life_months',
        'depreciation_method',
        'current_value',
        'warranty_until',
        'po_id',
        'grn_id',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'datetime',
        'purchase_date' => 'date',
        'warranty_until' => 'date',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'useful_life_months' => 'integer',
    ];

    /**
     * Get the product (master data)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the location
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the purchase order
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    /**
     * Get the goods receipt
     */
    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'grn_id');
    }

    /**
     * Get all movements for this asset
     */
    public function movements()
    {
        return $this->hasMany(AssetMovement::class)->orderBy('moved_at', 'desc');
    }

    /**
     * Get all tickets for this asset
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get maintenance schedules for this asset
     */
    public function maintenanceSchedules()
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    /**
     * Get the user assigned to this asset
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Calculate depreciation
     */
    public function calculateDepreciation()
    {
        if (!$this->purchase_price || !$this->useful_life_months || !$this->purchase_date) {
            return $this->purchase_price;
        }

        $monthsElapsed = now()->diffInMonths($this->purchase_date);
        
        if ($monthsElapsed >= $this->useful_life_months) {
            return 0; // Fully depreciated
        }

        if ($this->depreciation_method === 'STRAIGHT_LINE') {
            $monthlyDepreciation = $this->purchase_price / $this->useful_life_months;
            return max(0, $this->purchase_price - ($monthlyDepreciation * $monthsElapsed));
        }

        // Default to purchase price if no method
        return $this->purchase_price;
    }

    /**
     * Check if asset is under warranty
     */
    public function isUnderWarranty()
    {
        return $this->warranty_until && $this->warranty_until->isFuture();
    }

    /**
     * Check if asset is available for assignment
     */
    public function isAvailable()
    {
        return $this->status === 'AVAILABLE';
    }

    /**
     * Check if asset is assigned
     */
    public function isAssigned()
    {
        return in_array($this->status, ['ASSIGNED', 'IN_USE']);
    }
}
