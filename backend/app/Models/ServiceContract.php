<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'grn_id',
        'po_id',
        'product_id',
        'vendor_id',
        'location_id',
        'pic',
        'contract_type',
        'start_date',
        'end_date',
        'contract_value',
        'billing_cycle',
        'status',
        'notes',
        'renewal_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'renewal_date' => 'date',
        'contract_value' => 'decimal:2',
    ];

    protected $appends = ['is_expiring_soon', 'days_until_expiry'];

    /**
     * Relationships
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'grn_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeExpiring($query, $days = 30)
    {
        return $query->where('status', 'ACTIVE')
            ->whereRaw('end_date <= CURRENT_DATE + INTERVAL \'' . $days . ' days\'')
            ->whereRaw('end_date >= CURRENT_DATE');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'ACTIVE')
            ->whereDate('end_date', '<', now());
    }

    /**
     * Accessors
     */
    public function getIsExpiringSoonAttribute()
    {
        if ($this->status !== 'ACTIVE') {
            return false;
        }

        $daysUntil = now()->diffInDays($this->end_date, false);
        return $daysUntil <= 30 && $daysUntil >= 0;
    }

    public function getDaysUntilExpiryAttribute()
    {
        if ($this->status !== 'ACTIVE') {
            return null;
        }

        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Methods
     */
    public function calculateMonthlyValue()
    {
        if ($this->billing_cycle === 'ONE_TIME') {
            return $this->contract_value;
        }

        $months = match($this->billing_cycle) {
            'MONTHLY' => 1,
            'QUARTERLY' => 3,
            'YEARLY' => 12,
            default => 1
        };

        return $this->contract_value / $months;
    }
}
