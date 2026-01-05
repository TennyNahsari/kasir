<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    protected $inventoryService;
    protected $poService;

    public function __construct(
        InventoryService $inventoryService,
        PurchaseOrderService $poService
    ) {
        $this->inventoryService = $inventoryService;
        $this->poService = $poService;
    }

    /**
     * Create GRN from PO
     */
    public function createGRN(array $data, int $userId): GoodsReceipt
    {
        return DB::transaction(function () use ($data, $userId) {
            $po = PurchaseOrder::with('items.product')->findOrFail($data['po_id']);

            if (!in_array($po->status, ['SENT', 'APPROVED', 'PARTIALLY_RECEIVED'])) {
                throw new \Exception('PO must be sent or approved before receiving');
            }

            $grn = GoodsReceipt::create([
                'grn_no' => $this->generateGRNNo(),
                'po_id' => $po->id,
                'location_id' => $data['location_id'] ?? $po->location_id,
                'receipt_date' => $data['receipt_date'] ?? now(),
                'supplier_invoice_no' => $data['supplier_invoice_no'] ?? null,
                'supplier_invoice_date' => $data['supplier_invoice_date'] ?? null,
                'status' => 'DRAFT',
                'received_by' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create items from data or all outstanding PO items
            $items = $data['items'] ?? $this->getOutstandingPOItems($po);

            foreach ($items as $itemData) {
                $poItem = $po->items->find($itemData['po_item_id']);
                
                if (!$poItem) {
                    continue;
                }

                $quantityReceived = $itemData['quantity_received'];
                $quantityRejected = $itemData['quantity_rejected'] ?? 0;

                GoodsReceiptItem::create([
                    'grn_id' => $grn->id,
                    'po_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'quantity_ordered' => $poItem->quantity,
                    'quantity_received' => $quantityReceived,
                    'quantity_rejected' => $quantityRejected,
                    'unit_price' => $poItem->unit_price,
                    'line_total' => $poItem->unit_price * ($quantityReceived - $quantityRejected),
                    'quality_status' => 'PENDING',
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            return $grn->load('items.product', 'purchaseOrder.vendor', 'location');
        });
    }

    /**
     * Quality check GRN
     */
    public function qualityCheck(int $grnId, array $itemsQualityData, int $userId): GoodsReceipt
    {
        return DB::transaction(function () use ($grnId, $itemsQualityData, $userId) {
            $grn = GoodsReceipt::with('items')->findOrFail($grnId);

            if (!in_array($grn->status, ['DRAFT', 'QUALITY_CHECK'])) {
                throw new \Exception('GRN must be in draft or quality check status');
            }

            $items = $itemsQualityData['items'] ?? [];

            foreach ($items as $itemData) {
                $item = $grn->items->find($itemData['id']);
                
                if (!$item) {
                    continue;
                }

                $item->update([
                    'quality_status' => $itemData['quality_status'],
                    'quality_notes' => $itemData['quality_notes'] ?? null,
                    'quantity_rejected' => $itemData['quantity_rejected'] ?? $item->quantity_rejected,
                ]);
            }

            $grn->update([
                'status' => 'QUALITY_CHECK',
                'quality_checked_by' => $userId,
                'quality_checked_at' => now(),
                'quality_notes' => $itemsQualityData['grn_quality_notes'] ?? null,
            ]);

            return $grn->fresh('items.product');
        });
    }

    /**
     * Approve GRN
     */
    public function approveGRN(int $grnId, int $userId): GoodsReceipt
    {
        $grn = GoodsReceipt::with('items')->findOrFail($grnId);

        if (!in_array($grn->status, ['DRAFT', 'QUALITY_CHECK'])) {
            throw new \Exception('Only draft or quality-checked GRNs can be approved');
        }

        // Check all items have passed quality check
        $hasPendingQC = $grn->items->some(function ($item) {
            return $item->quality_status === 'PENDING';
        });

        if ($hasPendingQC) {
            throw new \Exception('All items must pass quality check before approval');
        }

        $grn->update([
            'status' => 'APPROVED',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $grn;
    }

    /**
     * Post GRN to inventory
     */
    public function postGRN(int $grnId, int $userId): GoodsReceipt
    {
        return DB::transaction(function () use ($grnId, $userId) {
            $grn = GoodsReceipt::with('items.product', 'purchaseOrder')->findOrFail($grnId);

            if ($grn->status !== 'APPROVED') {
                throw new \Exception('Only approved GRNs can be posted');
            }

            if ($grn->is_posted) {
                throw new \Exception('GRN already posted to inventory');
            }

            foreach ($grn->items as $item) {
                // Only post accepted quantity (received - rejected)
                $acceptedQuantity = $item->quantity_received - $item->quantity_rejected;

                if ($acceptedQuantity > 0 && $item->quality_status === 'PASSED') {
                    // Stock in to inventory
                    $this->inventoryService->stockIn(
                        $item->product_id,
                        $grn->location_id,
                        $acceptedQuantity,
                        $item->unit_price,
                        'GRN',
                        $grn->id,
                        $grn->grn_no,
                        "GRN from PO {$grn->purchaseOrder->po_no}",
                        $userId
                    );

                    // Update product average cost
                    $this->inventoryService->updateAverageCost(
                        $item->product_id,
                        $acceptedQuantity,
                        $item->unit_price
                    );

                    // Mark PO item as received
                    $this->poService->markItemReceived($item->po_item_id, $acceptedQuantity);
                }
            }

            $grn->update([
                'status' => 'POSTED',
                'is_posted' => true,
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            return $grn->fresh();
        });
    }

    /**
     * Reject GRN
     */
    public function rejectGRN(int $grnId, string $reason): GoodsReceipt
    {
        $grn = GoodsReceipt::findOrFail($grnId);

        if (!in_array($grn->status, ['DRAFT', 'QUALITY_CHECK'])) {
            throw new \Exception('Only draft or quality-checked GRNs can be rejected');
        }

        if ($grn->is_posted) {
            throw new \Exception('Cannot reject posted GRN');
        }

        $grn->update([
            'status' => 'REJECTED',
            'notes' => $grn->notes . "\nRejection reason: " . $reason,
        ]);

        return $grn;
    }

    /**
     * Get outstanding PO items (not fully received)
     */
    protected function getOutstandingPOItems(PurchaseOrder $po): array
    {
        $items = [];

        foreach ($po->items as $poItem) {
            $outstanding = $poItem->quantity - $poItem->quantity_received;
            
            if ($outstanding > 0) {
                $items[] = [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => $outstanding,
                    'quantity_rejected' => 0,
                ];
            }
        }

        return $items;
    }

    /**
     * Get GRNs by PO
     */
    public function getGRNsByPO(int $poId)
    {
        return GoodsReceipt::with(['items.product', 'receivedBy', 'approvedBy'])
            ->where('po_id', $poId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Generate unique GRN number
     */
    protected function generateGRNNo(): string
    {
        $date = now()->format('Ymd');
        $latest = GoodsReceipt::whereDate('created_at', now()->toDateString())
            ->latest('id')
            ->first();

        $sequence = $latest ? intval(substr($latest->grn_no, -4)) + 1 : 1;

        return 'GRN-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
