<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'location_id',
        'outlet_id',
        'customer_name',
        'whatsapp_number',
        'reservation_date',
        'reservation_time',
        'guest_count',
        'notes',
        'status',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'reservation_date' => 'date:Y-m-d',
        'confirmed_at' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public static function generateBookingCode()
    {
        $dateStr = now()->format('Ymd');
        $random = rand(100, 999);
        return 'BOOK-' . $dateStr . '-' . $random;
    }
}
