<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'manager_name',
        'phone',
        'email',
        'cost_center',
        'budget_limit',
        'is_active',
        'description',
    ];

    protected $casts = [
        'budget_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class, 'department', 'name');
    }
}
