<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'frequency',
        'last_maintenance_date',
        'next_maintenance_date',
        'auto_create_ticket',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'auto_create_ticket' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the asset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Calculate next maintenance date based on frequency
     */
    public function calculateNextMaintenanceDate(): \DateTime
    {
        $baseDate = $this->last_maintenance_date ?? $this->next_maintenance_date ?? now();

        return match($this->frequency) {
            'MONTHLY' => $baseDate->copy()->addMonth(),
            'QUARTERLY' => $baseDate->copy()->addMonths(3),
            'SEMI_ANNUAL' => $baseDate->copy()->addMonths(6),
            'ANNUAL' => $baseDate->copy()->addYear(),
            default => $baseDate->copy()->addMonths(3),
        };
    }

    /**
     * Check if maintenance is due soon (within 7 days)
     */
    public function isDueSoon(): bool
    {
        return $this->is_active 
            && $this->next_maintenance_date 
            && now()->diffInDays($this->next_maintenance_date, false) <= 7
            && now()->diffInDays($this->next_maintenance_date, false) >= 0;
    }

    /**
     * Check if maintenance is overdue
     */
    public function isOverdue(): bool
    {
        return $this->is_active 
            && $this->next_maintenance_date 
            && now()->isAfter($this->next_maintenance_date);
    }
}
