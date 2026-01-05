<?php

namespace App\Services;

use App\Models\InventoryTransfer;
use App\Models\InventoryTransferItem;
use Illuminate\Support\Facades\DB;

class TransferService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new transfer request
     */
    public function createTransfer(array $data, int $userId): InventoryTransfer
    {
        return DB::transaction(function () use ($data, $userId) {
            $transfer = InventoryTransfer::create([
                'transfer_no' => $this->generateTransferNo(),
                'from_location_id' => $data['from_location_id'],
                'to_location_id' => $data['to_location_id'],
                'transfer_date' => $data['transfer_date'] ?? now(),
                'status' => 'DRAFT',
                'requested_by' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create items
            foreach ($data['items'] as $item) {
                InventoryTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $transfer->load('items.product', 'fromLocation', 'toLocation');
        });
    }

    /**
     * Submit transfer for approval
     */
    public function submitTransfer(int $transferId): InventoryTransfer
    {
        return DB::transaction(function () use ($transferId) {
            $transfer = InventoryTransfer::with('items')->findOrFail($transferId);

            if ($transfer->status !== 'DRAFT') {
                throw new \Exception('Only draft transfers can be submitted');
            }

            // Check stock availability
            foreach ($transfer->items as $item) {
                $available = $this->inventoryService->getAvailableStock(
                    $item->product_id,
                    $transfer->from_location_id
                );

                if ($available < $item->quantity_requested) {
                    throw new \Exception("Insufficient stock for product ID {$item->product_id}");
                }
            }

            $transfer->update(['status' => 'PENDING']);

            return $transfer;
        });
    }

    /**
     * Approve transfer
     */
    public function approveTransfer(int $transferId, int $userId): InventoryTransfer
    {
        return DB::transaction(function () use ($transferId, $userId) {
            $transfer = InventoryTransfer::findOrFail($transferId);

            if ($transfer->status !== 'PENDING') {
                throw new \Exception('Only pending transfers can be approved');
            }

            $transfer->update([
                'status' => 'IN_TRANSIT',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            // Reserve stock at source location
            foreach ($transfer->items as $item) {
                $this->inventoryService->reserveStock(
                    $item->product_id,
                    $transfer->from_location_id,
                    $item->quantity_requested
                );
            }

            return $transfer;
        });
    }

    /**
     * Receive transfer at destination
     */
    public function receiveTransfer(int $transferId, array $receivedItems, int $userId): InventoryTransfer
    {
        return DB::transaction(function () use ($transferId, $receivedItems, $userId) {
            $transfer = InventoryTransfer::with('items')->findOrFail($transferId);

            if ($transfer->status !== 'IN_TRANSIT') {
                throw new \Exception('Only in-transit transfers can be received');
            }

            foreach ($receivedItems as $itemData) {
                $item = $transfer->items->find($itemData['id']);
                
                if (!$item) {
                    continue;
                }

                $quantityReceived = $itemData['quantity_received'];
                $quantityRejected = $itemData['quantity_rejected'] ?? 0;

                // Update item
                $item->update([
                    'quantity_received' => $quantityReceived,
                    'quantity_rejected' => $quantityRejected,
                    'notes' => $itemData['notes'] ?? $item->notes,
                ]);

                // Release reserved stock from source
                $this->inventoryService->releaseReservedStock(
                    $item->product_id,
                    $transfer->from_location_id,
                    $item->quantity_requested
                );

                // Stock out from source
                if ($quantityReceived > 0) {
                    $this->inventoryService->stockOut(
                        $item->product_id,
                        $transfer->from_location_id,
                        $quantityReceived,
                        'TRANSFER_OUT',
                        $transfer->id,
                        $transfer->transfer_no,
                        "Transfer to {$transfer->toLocation->name}",
                        $userId
                    );

                    // Stock in to destination
                    $this->inventoryService->stockIn(
                        $item->product_id,
                        $transfer->to_location_id,
                        $quantityReceived,
                        null,
                        'TRANSFER_IN',
                        $transfer->id,
                        $transfer->transfer_no,
                        "Transfer from {$transfer->fromLocation->name}",
                        $userId
                    );
                }
            }

            // Update transfer status
            $transfer->update([
                'status' => 'RECEIVED',
                'received_by' => $userId,
                'received_date' => now(),
            ]);

            return $transfer->fresh('items.product');
        });
    }

    /**
     * Cancel transfer
     */
    public function cancelTransfer(int $transferId, string $reason = null): InventoryTransfer
    {
        return DB::transaction(function () use ($transferId, $reason) {
            $transfer = InventoryTransfer::with('items')->findOrFail($transferId);

            if (!in_array($transfer->status, ['DRAFT', 'PENDING', 'IN_TRANSIT'])) {
                throw new \Exception('Cannot cancel this transfer');
            }

            // Release reserved stock if already reserved
            if ($transfer->status === 'IN_TRANSIT') {
                foreach ($transfer->items as $item) {
                    $this->inventoryService->releaseReservedStock(
                        $item->product_id,
                        $transfer->from_location_id,
                        $item->quantity_requested
                    );
                }
            }

            $transfer->update([
                'status' => 'CANCELLED',
                'notes' => $transfer->notes . "\nCancellation reason: " . ($reason ?? 'Not specified'),
            ]);

            return $transfer;
        });
    }

    /**
     * Generate unique transfer number
     */
    protected function generateTransferNo(): string
    {
        $date = now()->format('Ymd');
        $latest = InventoryTransfer::whereDate('created_at', now()->toDateString())
            ->latest('id')
            ->first();

        $sequence = $latest ? intval(substr($latest->transfer_no, -4)) + 1 : 1;

        return 'TRF-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
