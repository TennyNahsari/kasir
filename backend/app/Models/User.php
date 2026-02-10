<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'outlet_id',
        'location_id',
        'is_active',
        'is_technician',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_technician' => 'boolean',
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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Ticket relationships
    public function reportedTickets()
    {
        return $this->hasMany(Ticket::class, 'reported_by');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    // Note: assignedAssets relationship removed as assets no longer use assigned_to FK
    // Assets now use 'pic' string field instead

    // Role checks
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function isKitchen(): bool
    {
        return $this->role === 'kitchen';
    }

    public function isTechnician(): bool
    {
        return $this->is_technician;
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }
}
