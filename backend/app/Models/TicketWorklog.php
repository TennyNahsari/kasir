<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketWorklog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'worklog_type',
        'description',
        'time_spent_minutes',
        'is_internal',
    ];

    protected $casts = [
        'time_spent_minutes' => 'integer',
        'is_internal' => 'boolean',
    ];

    /**
     * Get the ticket
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user who created the worklog
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get attachments for this worklog
     */
    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'worklog_id');
    }
}
