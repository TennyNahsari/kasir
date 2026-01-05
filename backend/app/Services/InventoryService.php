<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\InventoryLedger;
use App\Models\Product;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Get current stock for a product at a location
     */
    public function getStock(int $productId, int $locationId): float
    {
        $stock = InventoryStock::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->first();

        return $stock ? $stock->quantity : 0;
    }

    /**
     * Get available stock (quantity - reserved)
     */
    public function getAvailableStock(int $productId, int $locationId): float
    {
        $stock = InventoryStock::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->first();

        return $stock ? ($stock->quantity - $stock->reserved_quantity) : 0;
    }

    /**
     * Stock IN - Increase stock quantity
     */
    public function stockIn(
        int $productId,
        int $locationId,
        float $quantity,
        float $unitCost = null,
        string $referenceType = null,
        int $referenceId = null,
        string $referenceNo = null,
        string $notes = null,
        int $userId = null
    ): InventoryLedger {
        return DB::transaction(function () use (
            $productId,
            $locationId,
            $quantity,
            $unitCost,
            $referenceType,
            $referenceId,
            $referenceNo,
            $notes,
            $userId
        ) {
            // Get or create inventory stock
            $stock = InventoryStock::firstOrCreate(
                [
                    'product_id' => $productId,
                    'location_id' => $locationId,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]
            );

            $balanceBefore = $stock->quantity;
            $balanceAfter = $balanceBefore + $quantity;

            // Update stock
            $stock->increment('quantity', $quantity);
            $stock->update(['last_stock_in' => now()]);

            // Create ledger entry
            $ledger = InventoryLedger::create([
                'product_id' => $productId,
                'location_id' => $locationId,
                'movement_type' => 'STOCK_IN',
                'quantity' => $quantity,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reference_no' => $referenceNo,
                'unit_cost' => $unitCost,
                'total_cost' => $unitCost ? $unitCost * $quantity : null,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            return $ledger;
        });
    }

    /**
     * Stock OUT - Decrease stock quantity
     */
    public function stockOut(
        int $productId,
        int $locationId,
        float $quantity,
        string $referenceType = null,
        int $referenceId = null,
        string $referenceNo = null,
        string $notes = null,
        int $userId = null
    ): InventoryLedger {
        return DB::transaction(function () use (
            $productId,
            $locationId,
            $quantity,
            $referenceType,
            $referenceId,
            $referenceNo,
            $notes,
            $userId
        ) {
            // Get inventory stock
            $stock = InventoryStock::where('product_id', $productId)
                ->where('location_id', $locationId)
                ->firstOrFail();

            // Check available stock
            $available = $stock->quantity - $stock->reserved_quantity;
            if ($available < $quantity) {
                throw new \Exception("Insufficient stock. Available: {$available}, Required: {$quantity}");
            }

            $balanceBefore = $stock->quantity;
            $balanceAfter = $balanceBefore - $quantity;

            // Update stock
            $stock->decrement('quantity', $quantity);
            $stock->update(['last_stock_out' => now()]);

            // Get average cost for COGS
            $product = Product::find($productId);
            $unitCost = $product->average_cost ?? 0;

            // Create ledger entry
            $ledger = InventoryLedger::create([
                'product_id' => $productId,
                'location_id' => $locationId,
                'movement_type' => 'STOCK_OUT',
                'quantity' => $quantity,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reference_no' => $referenceNo,
                'unit_cost' => $unitCost,
                'total_cost' => $unitCost * $quantity,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            return $ledger;
        });
    }

    /**
     * Reserve stock (for pending orders)
     */
    public function reserveStock(int $productId, int $locationId, float $quantity): void
    {
        DB::transaction(function () use ($productId, $locationId, $quantity) {
            $stock = InventoryStock::where('product_id', $productId)
                ->where('location_id', $locationId)
                ->firstOrFail();

            $available = $stock->quantity - $stock->reserved_quantity;
            if ($available < $quantity) {
                throw new \Exception("Insufficient stock to reserve. Available: {$available}, Required: {$quantity}");
            }

            $stock->increment('reserved_quantity', $quantity);
        });
    }

    /**
     * Release reserved stock
     */
    public function releaseReservedStock(int $productId, int $locationId, float $quantity): void
    {
        DB::transaction(function () use ($productId, $locationId, $quantity) {
            $stock = InventoryStock::where('product_id', $productId)
                ->where('location_id', $locationId)
                ->firstOrFail();

            $stock->decrement('reserved_quantity', $quantity);
        });
    }

    /**
     * Stock adjustment
     */
    public function adjustStock(
        int $productId,
        int $locationId,
        float $newQuantity,
        string $notes = null,
        int $userId = null
    ): InventoryLedger {
        return DB::transaction(function () use ($productId, $locationId, $newQuantity, $notes, $userId) {
            $stock = InventoryStock::firstOrCreate(
                [
                    'product_id' => $productId,
                    'location_id' => $locationId,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]
            );

            $balanceBefore = $stock->quantity;
            $difference = $newQuantity - $balanceBefore;

            // Update stock
            $stock->update(['quantity' => $newQuantity]);

            // Create ledger entry
            $ledger = InventoryLedger::create([
                'product_id' => $productId,
                'location_id' => $locationId,
                'movement_type' => 'ADJUSTMENT',
                'quantity' => abs($difference),
                'balance_before' => $balanceBefore,
                'balance_after' => $newQuantity,
                'reference_type' => 'ADJUSTMENT',
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            return $ledger;
        });
    }

    /**
     * Get stock levels with low stock alerts
     */
    public function getLowStockProducts(int $locationId = null)
    {
        $query = Product::where('track_inventory', true)
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'reorder_level')
            ->with(['category', 'inventoryStocks' => function ($q) use ($locationId) {
                if ($locationId) {
                    $q->where('location_id', $locationId);
                }
            }]);

        return $query->get();
    }

    /**
     * Calculate weighted average cost after stock in
     */
    public function updateAverageCost(int $productId, float $newQuantity, float $newCost): void
    {
        DB::transaction(function () use ($productId, $newQuantity, $newCost) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            $oldQuantity = $product->stock ?? 0;
            $oldCost = $product->average_cost ?? 0;

            if ($oldQuantity + $newQuantity > 0) {
                $totalCost = ($oldQuantity * $oldCost) + ($newQuantity * $newCost);
                $totalQuantity = $oldQuantity + $newQuantity;
                $averageCost = $totalCost / $totalQuantity;

                $product->update([
                    'average_cost' => $averageCost,
                    'last_purchase_price' => $newCost,
                ]);
            }
        });
    }
}
