<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_no',
        'request_date',
        'required_date',
        'status',
        'location_id',
        'department_id',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'required_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected $appends = ['requested_by_name', 'approved_by_name', 'pr_number', 'department'];

    // Accessors
    public function getRequestedByNameAttribute()
    {
        return $this->requestedBy ? $this->requestedBy->name : null;
    }

    public function getApprovedByNameAttribute()
    {
        return $this->approvedBy ? $this->approvedBy->name : null;
    }

    public function getPrNumberAttribute()
    {
        return $this->pr_no;
    }

    public function getDepartmentAttribute()
    {
        return $this->departmentRel ? $this->departmentRel->name : ($this->location ? $this->location->name : null);
    }

    // Relationships
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function departmentRel()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class, 'pr_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
