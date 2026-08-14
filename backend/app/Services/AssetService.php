<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetMovement;
use Illuminate\Support\Facades\DB;

class AssetService
{
    /**
     * Generate unique asset tag
     */
    public function generateAssetTag(): string
    {
        $year = now()->year;
        $lastAsset = Asset::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastAsset ? (int) substr($lastAsset->asset_tag, -3) + 1 : 1;
        
        return sprintf('AST-%d-%03d', $year, $sequence);
    }

    /**
     * Create a new asset
     */
    public function createAsset(array $data, int $userId): Asset
    {
        return DB::transaction(function () use ($data, $userId) {
            // Auto-generate asset tag if not provided
            if (!isset($data['asset_tag'])) {
                $data['asset_tag'] = $this->generateAssetTag();
            }

            // Set default status
            $data['status'] = $data['status'] ?? 'AVAILABLE';
            $data['condition'] = $data['condition'] ?? 'NEW';

            // Calculate current value
            if (isset($data['purchase_price'])) {
                $data['current_value'] = $data['purchase_price'];
            }

            // Create asset
            $asset = Asset::create($data);

            // Record movement
            $this->recordMovement([
                'asset_id' => $asset->id,
                'movement_type' => 'PURCHASED',
                'to_location_id' => $asset->location_id,
                'condition_after' => $asset->condition,
                'notes' => $data['notes'] ?? 'Asset purchased and received',
                'moved_by' => $userId,
            ]);

            return $asset->fresh();
        });
    }

    /**
     * Update asset
     */
    public function updateAsset(int $assetId, array $data, int $userId): Asset
    {
        $asset = Asset::findOrFail($assetId);
        
        // Track condition change
        $conditionChanged = isset($data['condition']) && $data['condition'] !== $asset->condition;
        $oldCondition = $asset->condition;

        $asset->update($data);

        // Recalculate depreciation if needed
        if ($asset->depreciation_method && $asset->purchase_date) {
            $asset->update([
                'current_value' => $asset->calculateDepreciation()
            ]);
        }

        // Record condition change
        if ($conditionChanged) {
            $this->recordMovement([
                'asset_id' => $asset->id,
                'movement_type' => 'MAINTENANCE',
                'condition_before' => $oldCondition,
                'condition_after' => $asset->condition,
                'notes' => $data['notes'] ?? 'Asset condition updated',
                'moved_by' => $userId,
            ]);
        }

        return $asset->fresh();
    }

    /**
     * Assign asset to PIC (Person In Charge)
     */
    public function assignAsset(int $assetId, string $pic, array $data, int $movedBy): Asset
    {
        return DB::transaction(function () use ($assetId, $pic, $data, $movedBy) {
            $asset = Asset::findOrFail($assetId);

            if (!$asset->isAvailable()) {
                throw new \Exception('Asset is not available for assignment');
            }

            $previousLocation = $asset->location_id;

            // Update asset
            $asset->update([
                'pic' => $pic,
                'assigned_date' => now(),
                'status' => 'ASSIGNED',
                'location_id' => $data['location_id'] ?? $asset->location_id,
            ]);

            // Record movement
            $this->recordMovement([
                'asset_id' => $asset->id,
                'movement_type' => 'ASSIGNED',
                'from_location_id' => $previousLocation,
                'to_location_id' => $asset->location_id,
                'condition_after' => $asset->condition,
                'notes' => $data['notes'] ?? "Asset assigned to PIC: {$pic}",
                'moved_by' => $movedBy,
            ]);

            return $asset->fresh();
        });
    }

    /**
     * Return asset from user
     */
    public function returnAsset(int $assetId, array $data, int $movedBy): Asset
    {
        return DB::transaction(function () use ($assetId, $data, $movedBy) {
            $asset = Asset::findOrFail($assetId);

            if (!$asset->isAssigned()) {
                throw new \Exception('Asset is not assigned');
            }

            $previousPic = $asset->pic;
            $previousLocation = $asset->location_id;

            // Determine new status based on condition
            $newStatus = 'AVAILABLE';
            if (isset($data['condition'])) {
                if (in_array($data['condition'], ['POOR', 'BROKEN'])) {
                    $newStatus = 'DAMAGED';
                } elseif ($data['needs_maintenance']) {
                    $newStatus = 'MAINTENANCE';
                }
            }

            // Update asset
            $asset->update([
                'pic' => null,
                'assigned_date' => null,
                'status' => $newStatus,
                'condition' => $data['condition'] ?? $asset->condition,
                'location_id' => $data['location_id'] ?? $asset->location_id,
            ]);

            // Record movement
            $this->recordMovement([
                'asset_id' => $asset->id,
                'movement_type' => 'RETURNED',
                'from_location_id' => $previousLocation,
                'to_location_id' => $asset->location_id,
                'condition_after' => $asset->condition,
                'notes' => $data['notes'] ?? "Asset returned from PIC: {$previousPic}",
                'moved_by' => $movedBy,
            ]);

            return $asset->fresh();
        });
    }

    /**
     * Transfer asset to another location
     */
    public function transferAsset(int $assetId, int $toLocationId, array $data, int $movedBy): Asset
    {
        return DB::transaction(function () use ($assetId, $toLocationId, $data, $movedBy) {
            $asset = Asset::findOrFail($assetId);
            $fromLocationId = $asset->location_id;

            $asset->update([
                'location_id' => $toLocationId,
            ]);

            $this->recordMovement([
                'asset_id' => $asset->id,
                'movement_type' => 'TRANSFERRED',
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'notes' => $data['notes'] ?? 'Asset transferred to new location',
                'moved_by' => $movedBy,
            ]);

            return $asset->fresh();
        });
    }

    /**
     * Mark asset as disposed
     */
    public function disposeAsset(int $assetId, array $data, int $movedBy): Asset
    {
        return DB::transaction(function () use ($assetId, $data, $movedBy) {
            $asset = Asset::findOrFail($assetId);

            $asset->update([
                'status' => 'DISPOSED',
                'pic' => null,
                'assigned_date' => null,
            ]);

            $this->recordMovement([
                'asset_id' => $asset->id,
                'movement_type' => 'DISPOSED',
                'notes' => $data['notes'] ?? 'Asset disposed',
                'moved_by' => $movedBy,
            ]);

            // Soft delete
            $asset->delete();

            return $asset;
        });
    }

    /**
     * Record asset movement
     */
    protected function recordMovement(array $data): AssetMovement
    {
        $data['moved_at'] = $data['moved_at'] ?? now();
        return AssetMovement::create($data);
    }

    /**
     * Get asset history
     */
    public function getAssetHistory(int $assetId)
    {
        return AssetMovement::where('asset_id', $assetId)
            ->with(['fromUser', 'toUser', 'fromLocation', 'toLocation', 'movedBy'])
            ->orderBy('moved_at', 'desc')
            ->get();
    }

    /**
     * Get assets by status
     */
    public function getAssetsByStatus(string $status)
    {
        return Asset::where('status', $status)
            ->with(['product', 'location'])
            ->get();
    }

    /**
     * Get assets assigned to user (by user ID or PIC search)
     */
    public function getUserAssets($userIdentifier)
    {
        $query = Asset::whereIn('status', ['ASSIGNED', 'IN_USE'])
            ->with(['product', 'location']);

        if (is_numeric($userIdentifier)) {
            $user = \App\Models\User::find((int)$userIdentifier);
            if ($user) {
                $query->where(function($q) use ($user) {
                    $q->where('pic', 'ilike', "%{$user->name}%")
                      ->orWhere('pic', 'ilike', "%{$user->email}%");
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $query->where('pic', 'ilike', "%{$userIdentifier}%");
        }

        return $query->get();
    }

    /**
     * Update asset depreciation (for scheduled job)
     */
    public function updateDepreciation()
    {
        $assets = Asset::whereNotNull('depreciation_method')
            ->whereNotNull('purchase_date')
            ->where('status', '!=', 'DISPOSED')
            ->get();

        foreach ($assets as $asset) {
            $currentValue = $asset->calculateDepreciation();
            $asset->update(['current_value' => $currentValue]);
        }

        return $assets->count();
    }

    /**
     * Add manual movement history entry
     */
    public function addManualMovement(int $assetId, array $data, int $userId)
    {
        return $this->recordMovement([
            'asset_id' => $assetId,
            'movement_type' => $data['movement_type'],
            'moved_at' => $data['moved_at'] ?? now(),
            'condition_after' => $data['condition_after'] ?? null,
            'notes' => $data['notes'],
            'moved_by' => $userId,
        ]);
    }
}
