<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_no',
        'outlet_id',
        'location_id',
        'business_type',
        'user_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'payment_details',
        'payment_proof',
        'booking_code',
        'customer_name',
        'notes',
        'table_id',
        'order_type',
        'status',
        'has_unconfirmed_addon',
        'addon_summary',
        'completed_at',
        'payment_due_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'payment_details' => 'array',
        'has_unconfirmed_addon' => 'boolean',
        'completed_at' => 'datetime',
        'payment_due_at' => 'datetime',
    ];

    // Relationships
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function cashFlows()
    {
        return $this->hasMany(CashFlow::class);
    }

    // Generate transaction number
    public static function generateTransactionNo(int $outletId): string
    {
        $date = now()->format('Ymd');
        $outlet = str_pad($outletId, 3, '0', STR_PAD_LEFT);
        
        $lastTransaction = self::withTrashed()
            ->where('outlet_id', $outletId)
            ->whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = 1;
        if ($lastTransaction && preg_match('/(\d{4})$/', $lastTransaction->transaction_no, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        do {
            $sequenceStr = str_pad($sequence, 4, '0', STR_PAD_LEFT);
            $trxNo = "TRX{$date}{$outlet}{$sequenceStr}";
            $exists = self::withTrashed()->where('transaction_no', $trxNo)->exists();
            if ($exists) {
                $sequence++;
            }
        } while ($exists);

        return $trxNo;
    }
}
