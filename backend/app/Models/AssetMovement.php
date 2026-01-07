<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'movement_type',
        'from_user_id',
        'to_user_id',
        'from_location_id',
        'to_location_id',
        'condition_before',
        'condition_after',
        'notes',
        'moved_by',
        'moved_at',
    ];

    protected $casts = [
        'moved_at' => 'datetime',
    ];

    /**
     * Get the asset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who moved from
     */
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the user who moved to
     */
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Get the location moved from
     */
    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    /**
     * Get the location moved to
     */
    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /**
     * Get the user who performed the movement
     */
    public function movedBy()
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}
