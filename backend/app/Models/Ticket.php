<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'type',
        'asset_id',
        'reported_by',
        'assigned_to',
        'location_id',
        'title',
        'description',
        'priority',
        'status',
        'category',
        'scheduled_date',
        'maintenance_type',
        'resolution_notes',
        'resolved_at',
        'resolved_by',
        'closed_at',
        'closed_by',
        'sla_due_date',
        'first_response_at',
        'estimated_completion',
        'rating',
        'feedback',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'sla_due_date' => 'datetime',
        'first_response_at' => 'datetime',
        'estimated_completion' => 'datetime',
        'rating' => 'integer',
    ];

    /**
     * Get the asset associated with the ticket
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who reported the ticket
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the user assigned to the ticket
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the location
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the user who resolved the ticket
     */
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the user who closed the ticket
     */
    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get all worklogs for this ticket
     */
    public function worklogs()
    {
        return $this->hasMany(TicketWorklog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all attachments for this ticket
     */
    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    /**
     * Check if ticket is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->sla_due_date || in_array($this->status, ['RESOLVED', 'CLOSED', 'CANCELLED'])) {
            return false;
        }

        return now()->isAfter($this->sla_due_date);
    }

    /**
     * Check if ticket can be resolved
     */
    public function canBeResolved(): bool
    {
        return in_array($this->status, ['ASSIGNED', 'IN_PROGRESS', 'ON_HOLD']);
    }

    /**
     * Check if ticket can be closed
     */
    public function canBeClosed(): bool
    {
        return $this->status === 'RESOLVED';
    }

    /**
     * Generate ticket number
     */
    public static function generateTicketNumber(): string
    {
        $year = date('Y');
        $lastTicket = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastTicket ? (int)substr($lastTicket->ticket_number, -4) + 1 : 1;

        return sprintf('TKT-%s-%04d', $year, $nextNumber);
    }

    /**
     * Calculate SLA due date based on priority
     */
    public static function calculateSLADueDate(string $priority): \DateTime
    {
        $hours = match($priority) {
            'HIGH' => 24,      // 24 hours
            'NORMAL' => 72,    // 72 hours (3 days)
            default => 72,
        };

        return now()->addHours($hours);
    }
}
